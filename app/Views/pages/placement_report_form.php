<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h3 class="card-title text-center mb-4 text-primary">Download Placement Report</h3>

                    <form id="reportForm" method="POST" target="downloadFrame" action="<?= base_url('placement/download') ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" id="from_date" name="from_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" id="to_date" name="to_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="reportSelect" class="form-label">Select Report</label>
                            <select id="reportSelect" name="report_type" class="form-select" required>
                                <option value="motor">Motor Placement</option>
                                <option value="chc">CHC Placement</option>
                                <option value="ihc">IHC Placement</option>
                                <option value="pa">PA Placement</option>
                                <option value="travel">Travel Placement</option>
                                <option value="pi">PI Placement</option>
                                <option value="pl">PL Placement</option>
                                <option value="car">CAR Placement</option>
                                <option value="fir">FIR Placement</option>
                                <option value="par">PAR Placement</option>
                            </select>
                        </div>

                        <input type="hidden" name="download" value="true" />

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4 form-control">
                                <i class="bi bi-download me-2"></i>Export Excel
                            </button>
                        </div>
                    </form>

                    <!-- Progress Bar -->
<!--                    <div id="progressBarWrapper" class="mt-4" style="display: none;">-->
<!--                        <div class="progress">-->
<!--                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"-->
<!--                                 role="progressbar" style="width: 100%">-->
<!--                                Generating report...-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <!-- Success Message -->
<!--                    <div id="successMessage" class="alert alert-success mt-4 text-center" style="display: none;">-->
<!--                        ✅ Report has been downloaded successfully.-->
<!--                    </div>-->

                    <!-- Hidden iframe to handle download -->
<!--                    <iframe id="downloadFrame" name="downloadFrame" style="display: none;"></iframe>-->

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress and Success Message Script -->
<!--<script>-->
<!--    const form = document.getElementById('reportForm');-->
<!--    const progressBar = document.getElementById('progressBarWrapper');-->
<!--    const successMessage = document.getElementById('successMessage');-->
<!---->
<!--    form.addEventListener('submit', function () {-->
<!--        // Show progress bar and hide success message-->
<!--        progressBar.style.display = 'block';-->
<!--        successMessage.style.display = 'none';-->
<!---->
<!--        // Simulate processing delay (adjust to your actual processing time)-->
<!--        setTimeout(() => {-->
<!--            progressBar.style.display = 'none';-->
<!--            successMessage.style.display = 'block';-->
<!---->
<!--            // Auto-hide success message after 5 seconds (optional)-->
<!--            setTimeout(() => {-->
<!--                successMessage.style.display = 'none';-->
<!--            }, 5000);-->
<!---->
<!--        }, 3000); // Adjust this duration as needed-->
<!--    });-->
<!--</script>-->

<?= $this->endSection() ?>
