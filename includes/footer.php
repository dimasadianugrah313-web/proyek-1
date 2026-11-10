<?php
// ============================================================================
// FOOTER TEMPLATE & MODAL LOGOUT KONFIRMASI (YA / BATAL)
// ============================================================================
?>
        </main>
    </div>
</div>

<!-- Modal Konfirmasi Logout (Ya / Batal) -->
<div class="modal-overlay" id="modalLogout" role="dialog" aria-modal="true" aria-labelledby="modalLogoutTitle">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalLogoutTitle">Konfirmasi Keluar</h3>
            <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center; padding: 24px 20px;">
            <p style="font-size: 15px; font-weight: 600; color: var(--cafe-espresso); margin-bottom: 8px;">
                Apakah Anda yakin ingin keluar?
            </p>
            <p style="font-size: 13px; color: var(--text-muted);">
                Sesi Anda saat ini akan diakhiri. Anda perlu memasukkan username dan password kembali untuk masuk.
            </p>
        </div>
        <div class="modal-footer" style="justify-content: center; gap: 12px; background: #FFF; padding-bottom: 20px;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="min-width: 90px;">
                Batal
            </button>
            <a href="<?= BASE_URL ?>/pages/logout.php" class="btn btn-danger" style="min-width: 90px;">
                Ya, Keluar
            </a>
        </div>
    </div>
</div>

<!-- Native JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
