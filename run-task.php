<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periodic AJAX Call</title>
</head>
<body>
    <br>
    <?php include_once './sections/navBtns.php' ?>
    <h1>Run Periodic AJAX Call</h1>

    <button onclick="startAjaxCall()">Start AJAX Call</button>
    <button onclick="stopAjaxCall()">Stop AJAX Call</button>

    <script>
        var ajaxInterval;

        function makeAjaxCall() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    console.log("Response from test.php: " + this.responseText);
                }
            };
            xhttp.open("GET", "test.php", true);
            xhttp.send();
        }

        function startAjaxCall() {
            makeAjaxCall(); // Initial call
            ajaxInterval = setInterval(makeAjaxCall, 300000);
            console.log("AJAX calls started.");
        }

        function stopAjaxCall() {
            clearInterval(ajaxInterval);
            console.log("AJAX calls stopped.");
        }
    </script>
</body>
</html>
