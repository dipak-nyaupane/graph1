<?php 

//index.php

$connect = new PDO("mysql:host=localhost;dbname=erp_hrm", "root", "");

$query = "SELECT a.branch_code,b.edesc FROM hr_employee_setup a,branch_code b WHERE a.branch_code=b.code GROUP BY a.branch_code ASC";

$statement = $connect->prepare($query);

$statement->execute();

$result = $statement->fetchAll();

?>




<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="library/bootstrap-5/bootstrap.min.css" rel="stylesheet" />
    <link href="library/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="library/daterangepicker.css" rel="stylesheet" />
    <script src="library/jquery.min.js"></script>
    <script src="library/bootstrap-5/bootstrap.bundle.min.js"></script>
    <script src="library/moment.min.js"></script>
    <script src="library/daterangepicker.min.js"></script>
    <script src="library/Chart.bundle.min.js"></script>
    <script src="library/jquery.dataTables.min.js"></script>
    <script src="library/dataTables.bootstrap5.min.js"></script>


</head>

<body>

    <div class="container-fluid">
        <h1 class="mt-2 mb-3 text-center text-primary">Attendance Graph</h1>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col col-sm-9">Attendance Data In Graph Format</div>
                    <div class="col col-sm-3">
                        <input type="text" id="daterange_textbox" class="form-control" readonly />
                        <select name="branch_code" class="form-control" id="branch_code">
                            <option value="">Selcect Branch Code</option>

                            <?php   foreach($result as $row)

                            {
                               echo '<option value="'.$row["branch_code"].'">'.$row["edesc"].'</option>';
                               

                           }
                       ?>
                        </select>
                    </div>


                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="chart-container pie-chart">
                        <canvas id="bar_chart" height="70"> </canvas>
                    </div>
                    <table class="table table-striped table-bordered" id="order_table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>present Day</th>
                                <th>Present Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script>
$(document).ready(function() {

    fetch_data();

    var sale_chart;

    function fetch_data(start_date = '', end_date = '',branch_code='') {
        var dataTable = $('#order_table').DataTable({
            "processing": true,
            "serverSide": true,
            //"order": [],
            "ajax": {
                url: "action.php",
                type: "POST",
                data: {
                    action: 'fetch',
                    start_date: start_date,
                    end_date: end_date,
                    branch_code:branch_code
                }
            },
            "drawCallback": function(settings) {
                var sales_date = [];
                var sale = [];

                for (var count = 0; count < settings.aoData.length; count++) {
                    sales_date.push(settings.aoData[count]._aData[2]);
                    sale.push(parseFloat(settings.aoData[count]._aData[1]));
                }

                var chart_data = {
                    labels: sales_date,
                    datasets: [{
                        label: 'Attendance',
                        backgroundColor: 'rgb(50, 200, 22)',
                        color: '#fff',
                        data: sale
                    }]
                };

                var group_chart3 = $('#bar_chart');

                if (sale_chart) {
                    sale_chart.destroy();
                }

                sale_chart = new Chart(group_chart3, {
                    type: 'bar',
                    data: chart_data
                });
            }
        });
    }


    $('#daterange_textbox').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                .endOf('month')
            ]
        },
        format: 'YYYY-MM-DD'
    }, function(start, end) {

        $('#order_table').DataTable().destroy();

        fetch_data(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        //fetch_data(branch_code);

    });

   

});
</script>