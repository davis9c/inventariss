    </main>
    <!-- End Page Content -->

    <!-- Footer -->
    <footer class="border-top bg-body-tertiary py-3">
        <div class="container-fluid text-center text-body-secondary small">
            Copyright &copy; Inventaris <?= date('Y') ?>
        </div>
    </footer>

</div>
<!-- End Page shell -->

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yakin ingin logout?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">Pilih "Logout" jika ingin mengakhiri sesi.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                <a class="btn btn-primary" href="<?= base_url('logout') ?>">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="confirmModalMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmModalOk">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container"></div>

</body>
</html>
