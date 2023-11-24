<?php 

$title = "Main";

require_once(__DIR__ . '/sections/head.php');
?>

<div class="container-fluid">
    <br><br><br>
    <div class="row">
        <div class="col-md-3">&nbsp;</div>
        <div class="col-md-6">
            <h1>Upload links csv</h1>
        </div>
        <div class="col-md-3">&nbsp;</div>
    </div>
    <form name="import-csv" action="upload.php" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-3">&nbsp;</div>
            <div class="col-md-6">
                <hr />
                    <div id="import-csv-div">
                        <label for="import-csv">Excel File: </label>
                        <input type="file" name="import-csv" id="import-csv" accept=".csv" />
                    </div>
                <hr />
            </div>

            <div class="col-md-3">&nbsp;</div>

            <div class="col-md-8">&nbsp;</div>

            <div class="col-md-4">
                <div>
                    <input id="import-btn" class="btn btn-primary" type="submit" value="Import" name="submit" onclick="uploadFile()" />
                </div>
            </div>
        </div>
    </form>
</div>

<?php

require_once(__DIR__ . '/sections/footer.php')
?>