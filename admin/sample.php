<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 20px;
        }
        .card-title {
            font-size: 1.5rem;
        }
        .card-icon {
            font-size: 3rem;
            color: #6c757d;
        }
        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .total-count {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Dashboard</h1>
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Authors</h5>
                            <p class="total-count" id="totalAuthors">0</p>
                            <p>BSCS: <span id="bscsAuthors">0</span></p>
                            <p>BSIT: <span id="bsitAuthors">0</span></p>
                        </div>
                        <i class="card-icon fas fa-user"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Instructors</h5>
                            <p class="total-count" id="totalInstructors">0</p>
                        </div>
                        <i class="card-icon fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Documents</h5>
                            <p class="total-count" id="totalDocuments">0</p>
                            <p>Published: <span id="publishedDocuments">0</span></p>
                            <p>For Approval: <span id="approvalDocuments">0</span></p>
                        </div>
                        <i class="card-icon fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Reports</h5>
                            <p class="total-count" id="totalReports">0</p>
                        </div>
                        <i class="card-icon fas fa-chart-bar"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Most Used Keywords</h5>
                        <canvas id="keywordsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    </script>
</body>
</html>
