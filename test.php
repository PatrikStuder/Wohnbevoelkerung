<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Testing Pie Chart</title>
    <!--<script type="text/javascript" src="d3/d3.v2.js"></script>-->
    <script src="http://d3js.org/d3.v2.js"></script>
    <!-- Note: I made good use of the sample code provided by the D3JS community and extended it to fit my needs to create this simple dashboard -->
    <style type="text/css">


        #pieChart {
            position:absolute;
            top:10px;
            left:10px;
            width:400px;
            height: 400px;
        }



        #lineChart {
            position:absolute;
            top:10px;
            left:410px;
            height: 150px;
        }

        #barChart {
            position:absolute;
            top:160px;
            left:410px;
            height: 250px;
        }

        .slice {
            font-size: 12pt;
            font-family: Verdana;
            fill: white; //svg specific - instead of color
        font-weight: bold;
        }

        /*for line chart*/
        .axis path, .axis line {
            fill: none;
            stroke: black;
            shape-rendering: crispEdges; //The shape-rendering property is an SVG attribute, used here to make sure our axis and its tick mark lines are pixel-perfect.
        }

        .line {
            fill: none;
            /*stroke: steelblue;*/
            stroke-width: 3px;
        }

        .dot {
            /*fill: white;*/
            /*stroke: steelblue;*/
            stroke-width: 1.5px;
        }


        .axis text {
            font-family: Verdana;
            font-size: 11px;
        }

        .title {
            font-family: Verdana;
            font-size: 15px;

        }

        .xAxis {
            font-family: verdana;
            font-size: 11px;
            fill: black;
        }

        .yAxis {
            font-family: verdana;
            font-size: 11px;
            fill: white;
        }


        table {
            border-collapse:collapse;
            border: 0px;
            font-family: Verdana;
            color: #5C5558;
            font-size: 12px;
            text-align: right;
        }

        td {
            padding-left: 10px;
        }

        #lineChartTitle1 {
            font-family: Verdana;
            font-size  : 14px;
            fill       : lightgrey;
            font-weight: bold;
            text-anchor: middle;
        }

        #lineChartTitle2 {
            font-family: Verdana;
            font-size  : 72px;
            fill       : grey;
            text-anchor: middle;
            font-weight: bold;
            /*font-style: italic;*/
        }

    </style>
</head>
<body>

<div id="pieChart"></div>
<div id="barChart"></div>
<div id="lineChart"></div>
<script type="text/javascript">

    /*
    ################ FORMATS ##################
    -------------------------------------------
    */


    var 	formatAsPercentage = d3.format("%"),
        formatAsPercentage1Dec = d3.format(".1%"),
        formatAsInteger = d3.format(","),
        fsec = d3.time.format("%S s"),
        fmin = d3.time.format("%M m"),
        fhou = d3.time.format("%H h"),
        fwee = d3.time.format("%a"),
        fdat = d3.time.format("%d d"),
        fmon = d3.time.format("%b")
    ;

    /*
    ############# PIE CHART ###################
    -------------------------------------------
    */



    function dsPieChart(){

        var dataset = [
                {category: "Mann", measure: 0.502},
                {category: "Frau", measure: 0.498},

            ]
        ;

        var 	width = 400,
            height = 400,
            outerRadius = Math.min(width, height) / 2,
            innerRadius = outerRadius * .999,
            // for animation
            innerRadiusFinal = outerRadius * .5,
            innerRadiusFinal3 = outerRadius* .45,
            color = d3.scale.category20()    //builtin range of colors
        ;

        var vis = d3.select("#pieChart")
            .append("svg:svg")              //create the SVG element inside the <body>
            .data([dataset])                   //associate our data with the document
            .attr("width", width)           //set the width and height of our visualization (these will be attributes of the <svg> tag
            .attr("height", height)
            .append("svg:g")                //make a group to hold our pie chart
            .attr("transform", "translate(" + outerRadius + "," + outerRadius + ")")    //move the center of the pie chart from 0, 0 to radius, radius
        ;

        var arc = d3.svg.arc()              //this will create <path> elements for us using arc data
            .outerRadius(outerRadius).innerRadius(innerRadius);

        // for animation
        var arcFinal = d3.svg.arc().innerRadius(innerRadiusFinal).outerRadius(outerRadius);
        var arcFinal3 = d3.svg.arc().innerRadius(innerRadiusFinal3).outerRadius(outerRadius);

        var pie = d3.layout.pie()           //this will create arc data for us given a list of values
            .value(function(d) { return d.measure; });    //we must tell it out to access the value of each element in our data array

        var arcs = vis.selectAll("g.slice")     //this selects all <g> elements with class slice (there aren't any yet)
            .data(pie)                          //associate the generated pie data (an array of arcs, each having startAngle, endAngle and value properties)
            .enter()                            //this will create <g> elements for every "extra" data element that should be associated with a selection. The result is creating a <g> for every object in the data array
            .append("svg:g")                //create a group to hold each slice (we will have a <path> and a <text> element associated with each slice)
            .attr("class", "slice")    //allow us to style things in the slices (like text)
            .on("mouseover", mouseover)
            .on("mouseout", mouseout)
            .on("click", up)
        ;

        arcs.append("svg:path")
            .attr("fill", function(d, i) { return color(i); } ) //set the color for each slice to be chosen from the color function defined above
            .attr("d", arc)     //this creates the actual SVG path using the associated data (pie) with the arc drawing function
            .append("svg:title") //mouseover title showing the figures
            .text(function(d) { return d.data.category + ": " + formatAsPercentage(d.data.measure); });

        d3.selectAll("g.slice").selectAll("path").transition()
            .duration(750)
            .delay(10)
            .attr("d", arcFinal )
        ;

        // Add a label to the larger arcs, translated to the arc centroid and rotated.
        // source: http://bl.ocks.org/1305337#index.html
        arcs.filter(function(d) { return d.endAngle - d.startAngle > .2; })
            .append("svg:text")
            .attr("dy", ".35em")
            .attr("text-anchor", "middle")
            .attr("transform", function(d) { return "translate(" + arcFinal.centroid(d) + ")rotate(" + angle(d) + ")"; })
            //.text(function(d) { return formatAsPercentage(d.value); })
            .text(function(d) { return d.data.category; })
        ;

        // Computes the label angle of an arc, converting from radians to degrees.
        function angle(d) {
            var a = (d.startAngle + d.endAngle) * 90 / Math.PI - 90;
            return a > 90 ? a - 180 : a;
        }


        // Pie chart title
        vis.append("svg:text")
            .attr("dy", ".35em")
            .attr("text-anchor", "middle")
            .text("Geschlechtsanteil")
            .attr("class","title")
        ;



        function mouseover() {
            d3.select(this).select("path").transition()
                .duration(750)
                //.attr("stroke","red")
                //.attr("stroke-width", 1.5)
                .attr("d", arcFinal3)
            ;
        }

        function mouseout() {
            d3.select(this).select("path").transition()
                .duration(750)
                //.attr("stroke","blue")
                //.attr("stroke-width", 1.5)
                .attr("d", arcFinal)
            ;
        }

        function up(d, i) {

            /* update bar chart when user selects piece of the pie chart */
            //updateBarChart(dataset[i].category);
            updateBarChart(d.data.category, color(i));
            updateLineChart(d.data.category, color(i));

        }
    }

    dsPieChart();

    /*
    ############# BAR CHART ###################
    -------------------------------------------
    */



    var datasetBarChart = [
        { group: "All", category: 2015, measure: 266510 },
        { group: "All", category: 2016, measure: 269731},
        { group: "All", category: 2017, measure: 272780 },
        { group: "Frau", category: 2015, measure: 132602 },
        { group: "Frau", category: 2016, measure: 134178 },
        { group: "Frau", category: 2017, measure: 135628 },
        { group: "Mann", category: 2015, measure: 133908 },
        { group: "Mann", category: 2016, measure: 135553 },
        { group: "Mann", category: 2017, measure: 137152},

        ]
    ;

    // set initial group value
    var group = "All";

    function datasetBarChosen(group) {
        var ds = [];
        for (x in datasetBarChart) {
            if(datasetBarChart[x].group==group){
                ds.push(datasetBarChart[x]);
            }
        }
        return ds;
    }


    function dsBarChartBasics() {

        var margin = {top: 30, right: 5, bottom: 20, left: 50},
            width = 500 - margin.left - margin.right,
            height = 250 - margin.top - margin.bottom,
            colorBar = d3.scale.category20(),
            barPadding = 1
        ;

        return {
            margin : margin,
            width : width,
            height : height,
            colorBar : colorBar,
            barPadding : barPadding
        }
            ;
    }

    function dsBarChart() {

        var firstDatasetBarChart = datasetBarChosen(group);

        var basics = dsBarChartBasics();

        var margin = basics.margin,
            width = basics.width,
            height = basics.height,
            colorBar = basics.colorBar,
            barPadding = basics.barPadding
        ;

        var 	xScale = d3.scale.linear()
            .domain([0, firstDatasetBarChart.length])
            .range([0, width])
        ;

        // Create linear y scale
        // Purpose: No matter what the data is, the bar should fit into the svg area; bars should not
        // get higher than the svg height. Hence incoming data needs to be scaled to fit into the svg area.
        var yScale = d3.scale.linear()
            // use the max funtion to derive end point of the domain (max value of the dataset)
            // do not use the min value of the dataset as min of the domain as otherwise you will not see the first bar
                .domain([0, d3.max(firstDatasetBarChart, function(d) { return d.measure; })])
                // As coordinates are always defined from the top left corner, the y position of the bar
                // is the svg height minus the data value. So you basically draw the bar starting from the top.
                // To have the y position calculated by the range function
                .range([height, 0])
        ;

        //Create SVG element

        var svg = d3.select("#barChart")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .attr("id","barChartPlot")
        ;

        var plot = svg
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
        ;

        plot.selectAll("rect")
            .data(firstDatasetBarChart)
            .enter()
            .append("rect")
            .attr("x", function(d, i) {
                return xScale(i);
            })
            .attr("width", width / firstDatasetBarChart.length - barPadding)
            .attr("y", function(d) {
                return yScale(d.measure);
            })
            .attr("height", function(d) {
                return height-yScale(d.measure);
            })
            .attr("fill", "lightgrey")
        ;


        // Add y labels to plot

        plot.selectAll("text")
            .data(firstDatasetBarChart)
            .enter()
            .append("text")
            .text(function(d) {
                return formatAsInteger(d3.round(d.measure));
            })
            .attr("text-anchor", "middle")
            // Set x position to the left edge of each bar plus half the bar width
            .attr("x", function(d, i) {
                return (i * (width / firstDatasetBarChart.length)) + ((width / firstDatasetBarChart.length - barPadding) / 2);
            })
            .attr("y", function(d) {
                return yScale(d.measure) + 14;
            })
            .attr("class", "yAxis")
        /* moved to CSS
        .attr("font-family", "sans-serif")
        .attr("font-size", "11px")
        .attr("fill", "white")
        */
        ;

        // Add x labels to chart

        var xLabels = svg
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + (margin.top + height)  + ")")
        ;

        xLabels.selectAll("text.xAxis")
            .data(firstDatasetBarChart)
            .enter()
            .append("text")
            .text(function(d) { return d.category;})
            .attr("text-anchor", "middle")
            // Set x position to the left edge of each bar plus half the bar width
            .attr("x", function(d, i) {
                return (i * (width / firstDatasetBarChart.length)) + ((width / firstDatasetBarChart.length - barPadding) / 2);
            })
            .attr("y", 15)
            .attr("class", "xAxis")
        //.attr("style", "font-size: 12; font-family: Helvetica, sans-serif")
        ;

        // Title

        svg.append("text")
            .attr("x", (width + margin.left + margin.right)/2)
            .attr("y", 15)
            .attr("class","title")
            .attr("text-anchor", "middle")
            .text("Einwohnerzahlen")
        ;
    }

    dsBarChart();

    /* ** UPDATE CHART ** */

    /* updates bar chart on request */

    function updateBarChart(group, colorChosen) {

        var currentDatasetBarChart = datasetBarChosen(group);

        var basics = dsBarChartBasics();

        var margin = basics.margin,
            width = basics.width,
            height = basics.height,
            colorBar = basics.colorBar,
            barPadding = basics.barPadding
        ;

        var 	xScale = d3.scale.linear()
            .domain([0, currentDatasetBarChart.length])
            .range([0, width])
        ;


        var yScale = d3.scale.linear()
            .domain([0, d3.max(currentDatasetBarChart, function(d) { return d.measure; })])
            .range([height,0])
        ;

        var svg = d3.select("#barChart svg");

        var plot = d3.select("#barChartPlot")
            .datum(currentDatasetBarChart)
        ;

        /* Note that here we only have to select the elements - no more appending! */
        plot.selectAll("rect")
            .data(currentDatasetBarChart)
            .transition()
            .duration(750)
            .attr("x", function(d, i) {
                return xScale(i);
            })
            .attr("width", width / currentDatasetBarChart.length - barPadding)
            .attr("y", function(d) {
                return yScale(d.measure);
            })
            .attr("height", function(d) {
                return height-yScale(d.measure);
            })
            .attr("fill", colorChosen)
        ;

        plot.selectAll("text.yAxis") // target the text element(s) which has a yAxis class defined
            .data(currentDatasetBarChart)
            .transition()
            .duration(750)
            .attr("text-anchor", "middle")
            .attr("x", function(d, i) {
                return (i * (width / currentDatasetBarChart.length)) + ((width / currentDatasetBarChart.length - barPadding) / 2);
            })
            .attr("y", function(d) {
                return yScale(d.measure) + 14;
            })
            .text(function(d) {
                return formatAsInteger(d3.round(d.measure));
            })
            .attr("class", "yAxis")
        ;


        svg.selectAll("text.title") // target the text element(s) which has a title class defined
            .attr("x", (width + margin.left + margin.right)/2)
            .attr("y", 15)
            .attr("class","title")
            .attr("text-anchor", "middle")
            .text("Einwohnerzahl " + group)
        ;
    }




</script>
</body>
</html>