<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container" style="max-width: 480px; margin-top: 15vh;">
    <div class="card shadow-lg w-100" style="max-width: 600px;">
        <div class="text-center">
            <br>
            <h4 class="mb-0">Quote Listing Report</h4>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger text-center">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form id="quoteForm" method="GET" target="downloadFrame" action="<?= base_url('quote-report/download') ?>">
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                    </div>
                    <div class="col-md-6">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-2"></i> Export Excel
                    </button>
                </div>
            </form>

            <!-- Progress Bar -->
<!--            <div id="progressBarWrapper" class="mt-4" style="display: none;">-->
<!--                <div class="progress">-->
<!--                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"-->
<!--                         role="progressbar" style="width: 100%">-->
<!--                        Generating report...-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

            <!-- Success Message -->
<!--            <div id="successMessage" class="alert alert-success mt-4 text-center" style="display: none;">-->
<!--                ✅ Report has been downloaded successfully.-->
<!--            </div>-->

            <!-- Hidden iframe for download -->
<!--            <iframe id="downloadFrame" name="downloadFrame" style="display: none;"></iframe>-->
        </div>
    </div>
</div>

<!-- JavaScript to show progress and success -->
<!--<script>-->
<!--    const form = document.getElementById('quoteForm');-->
<!--    const progressBar = document.getElementById('progressBarWrapper');-->
<!--    const successMessage = document.getElementById('successMessage');-->
<!---->
<!--    form.addEventListener('submit', function () {-->
<!--        progressBar.style.display = 'block';-->
<!--        successMessage.style.display = 'none';-->
<!---->
<!--        // Simulate processing and download complete-->
<!--        setTimeout(() => {-->
<!--            progressBar.style.display = 'none';-->
<!--            successMessage.style.display = 'block';-->
<!---->
<!--            setTimeout(() => {-->
<!--                successMessage.style.display = 'none';-->
<!--            }, 5000);-->
<!--        }, 3000);-->
<!--    });-->
<!--</script>-->

<?= $this->endSection() ?>
