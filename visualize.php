<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Status Dashboard</title>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        /* Add some basic styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        #dashboard {
            width: 800px;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .bar {
            fill: steelblue;
        }

        .bar:hover {
            fill: orange;
        }

        .axis-x text {
            transform: rotate(-45deg);
            transform-origin: end center;
            text-anchor: end;
        }

        .tooltip {
            position: absolute;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 5px;
            pointer-events: none;
            opacity: 0;
            /* Adjust position to top right */
            top: 10px;
            right: 10px;
            color: darkorange;
        }
    </style>
</head>
<body>
<br>
<br>
<?php include_once './sections/navBtns.php' ?>
<br>

<div id="dashboard">
    <h1>Call Status Dashboard</h1>
    <div id="chart-container"></div>
    <p>This is a sample dashboard showing call status data. You can add more content here.</p>
</div>

<script>
// Sample data
// var data = [
//     { label: 'Category A', value: 30 },
//     { label: 'Category B', value: 50 },
//     { label: 'Category C', value: 20 },
//     // Add more data as needed
// ];

// Fetch data from PHP endpoint
d3.json('data.php?query=all')
    .then(function(data) {

        // Set up dimensions for the chart
        var width = 600;
        var height = 400;
        var margin = { top: 20, right: 20, bottom: 70, left: 70 };

        // Create SVG container
        var svg = d3.select("#chart-container")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

        // Set up scales
        var xScale = d3.scaleBand()
            .domain(data.map(function(d) { return d.call_status; }))
            .range([0, width])
            .padding(0.1);

        var yScale = d3.scaleLinear()
            .domain([0, d3.max(data, function(d) { return d.count; })])
            .range([height, 0]);

        // Create bars
        svg.selectAll(".bar")
            .data(data)
            .enter().append("rect")
            .attr("class", "bar")
            .attr("x", function(d) { return xScale(d.call_status); })
            .attr("width", xScale.bandwidth())
            .attr("y", function(d) { return yScale(d.count); })
            .attr("height", function(d) { return height - yScale(d.count); })
            .on("mouseover", function(e,d) {
                // console.log(d);
                tooltip.style("opacity", 1);
                tooltip.html("Total " + d.call_status + " : " + d.count)
                    .style("left", "auto")
                    .style("right", "400px")
                    .style("top", "10px");
            })
            .on("mouseout", function(d) {
                tooltip.style("opacity", 0);
            });

        // Add x-axis
        svg.append("g")
            .attr("class", "axis-x")
            .attr("transform", "translate(0," + height + ")")
            .call(d3.axisBottom(xScale))
            .selectAll("text")
                .style("text-anchor", "end");

        // Add y-axis
        svg.append("g")
            .call(d3.axisLeft(yScale));

        // Create tooltip
        var tooltip = d3.select("#chart-container")
            .append("div")
            .attr("class", "tooltip");
    })
    .catch((error) => {
        console.error('Error fetching data:', error);
    });

</script>

</body>
</html>
