<?php

namespace App\Controllers;

use App\Models\AccessProfileModel;
use App\Models\AccessProfileLocationModel;
use App\Models\UserAccessProfileModel;
use App\Models\LocationModel;

class AccessProfile extends BaseController
{
    protected AccessProfileModel $profileModel;
    protected AccessProfileLocationModel $profileLocModel;
    protected UserAccessProfileModel $userProfileModel;
    protected LocationModel $locationModel;

    public function __construct()
    {
        $this->profileModel     = new AccessProfileModel();
        $this->profileLocModel  = new AccessProfileLocationModel();
        $this->userProfileModel = new UserAccessProfileModel();
        $this->locationModel    = new LocationModel();
    }

    public function index()
    {
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'access_profiles',
                static fn ($b) => $b->select('access_profiles.*'),
                ['name', 'description'],
                [0 => 'name', 1 => 'description', 2 => 'created_at'],
                'name'
            ));
        }

        return view('access_profiles/index', [
            'title'     => 'Profile Akses',
            'locations' => $this->locationModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function data($id)
    {
        $profile = $this->profileModel->find($id);

        if (! $profile) {
            return $this->respondError('Profile tidak ditemukan.', 404);
        }

        $profile['location_ids'] = array_map(
            'intval',
            array_column(
                $this->profileLocModel->where('profile_id', $id)->findAll(),
                'location_id'
            )
        );

        $profile['user_count'] = $this->userProfileModel
            ->where('profile_id', $id)
            ->countAllResults();

        return $this->respondAjax($profile);
    }

    public function create()
    {
        return redirect()->to('/access-profiles?create=1');
    }

    public function store()
    {
        $isAjax = $this->request->isAJAX();

        $db = db_connect();
        $db->transStart();

        $this->profileModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        $profileId = $this->profileModel->getInsertID();

        $locations = $this->request->getPost('location_ids');
        if (! empty($locations) && is_array($locations)) {
            foreach ($locations as $locationId) {
                $this->profileLocModel->insert([
                    'profile_id'  => $profileId,
                    'location_id' => $locationId,
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Profile gagal ditambahkan.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Profile gagal ditambahkan.');
        }

        if ($isAjax) {
            return $this->respondSuccess('Profile berhasil ditambahkan.', [
                'id' => $profileId,
            ]);
        }

        return redirect()
            ->to('/access-profiles')
            ->with('success', 'Profile berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return redirect()->to('/access-profiles?edit=' . (int) $id);
    }

    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $profile = $this->profileModel->find($id);

        if (! $profile) {
            if ($isAjax) {
                return $this->respondError('Profile tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transStart();

        $this->profileModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        // Hapus relasi lokasi lama
        $this->profileLocModel->where('profile_id', $id)->delete();

        // Simpan relasi lokasi baru
        $locations = $this->request->getPost('location_ids');
        if (! empty($locations) && is_array($locations)) {
            foreach ($locations as $locationId) {
                $this->profileLocModel->insert([
                    'profile_id'  => $id,
                    'location_id' => $locationId,
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isAjax) {
                return $this->respondError('Profile gagal diperbarui.', 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Profile gagal diperbarui.');
        }

        // Sinkronkan semua user yang memakai profile ini
        $userIds = array_column(
            $this->userProfileModel
                ->where('profile_id', $id)
                ->findAll(),
            'user_id'
        );

        if ($userIds !== []) {
            sync_user_locations($userIds);
        }

        if ($isAjax) {
            return $this->respondSuccess('Profile berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->to('/access-profiles')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    public function delete($id)
    {
        $isAjax = $this->request->isAJAX();

        $profile = $this->profileModel->find($id);

        if (! $profile) {
            if ($isAjax) {
                return $this->respondError('Profile tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil user terpengaruh sebelum relasi dihapus (FK cascade)
        $userIds = array_column(
            $this->userProfileModel
                ->where('profile_id', $id)
                ->findAll(),
            'user_id'
        );

        $this->profileModel->delete($id);

        // Sinkronkan semua user yang tadinya memakai profile ini
        if ($userIds !== []) {
            sync_user_locations($userIds);
        }

        if ($isAjax) {
            return $this->respondSuccess('Profile berhasil dihapus.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()
            ->to('/access-profiles')
            ->with('success', 'Profile berhasil dihapus.');
    }
}