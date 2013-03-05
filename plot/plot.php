<?php
///reference guide to phpplot
require_once 'phplot.php'; 
$plot = new PHPlot(800,600); // width x height
$plot->SetPlotType('linepoints'); // can be bars,pie,linepoints,area,points,thinbarline,lines
$plot->SetDataType('text-data');
/*
text-data => ('label',y1,y2,y3)
data-data => ('label',x,y1,y2,y3) same as text-data but x value given best for bar graph
data-data-error => ('label',x,y1,e1-,e1+,y2,e2-,e2+) same as data-data but can give error values must be positive
text-data-single => ('label'=>single value) pie chart
*/
//data is x, y1,y2,y3,y4 so just add more y's
$example_data = array(
     array('a',3,4,2),
     array('b',5,'',1),  // here we have a missing data point, that's ok
     array('c',7,2,6),
     array('d',8,1,4),
     array('e',2,4,6),
     array('f',6,4,5),
     array('g',7,2,3)
);
$plot->SetDataValues($example_data);

//Set titles
$plot->SetTitle("A Simple Plot\nMade with PHPlot");
$plot->SetXTitle('X Data');
$plot->SetYTitle('Y Data');

//Turn off X axis ticks and labels because they get in the way
$plot->SetXTickPos('none'); // vertical and horizontal line are smooth out
$plot->SetLineWidths(1); //line width
$plot->SetDrawXGrid(True); // draw x grid
//$plot->SetLegendPosition(0, 0, 'image', 0, 0, 5, 5); (x,y) top left 0,0 bot right 1,1
$plot->DrawGraph();

/*AREA PLOT -----------------------------------------------------------
require_once 'phplot.php';

$data = array(
  array('1960', 100, 70, 60, 54, 16,  2),
  array('1970', 100, 80, 63, 54, 22, 20),
  array('1980', 100, 80, 66, 54, 27, 25),
  array('1990', 100, 95, 69, 54, 28, 10),
  array('2000', 100, 72, 72, 54, 38,  5),
);

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('area');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Candy Sales by Flavor');

# Set Y data limits, tick increment, and titles:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 100);
$plot->SetYTickIncrement(10);
$plot->SetYTitle('% of Total');
$plot->SetXTitle('Year');

# Colors are significant to this data:
$plot->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan', 'magenta'));
$plot->SetLegend(array('Cherry', 'Lime', 'Lemon', 'Banana', 'Apple', 'Berry'));

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

$plot->DrawGraph();
-----------------------------------------------------------------------

//BAR GRAPH------------------------------------------------------------
require_once 'phplot.php';

$data = array(
  array('Jan', 40, 2, 4), array('Feb', 30, 3, 4), array('Mar', 20, 4, 4),
  array('Apr', 10, 5, 4), array('May',  3, 6, 4), array('Jun',  7, 7, 4),
  array('Jul', 10, 8, 4), array('Aug', 15, 9, 4), array('Sep', 20, 5, 4),
  array('Oct', 18, 4, 4), array('Nov', 16, 7, 4), array('Dec', 14, 3, 4),
);

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Shaded Bar Chart with 3 Data Sets');

# Make a legend for the 3 data sets plotted:
$plot->SetLegend(array('Engineering', 'Manufacturing', 'Administration'));

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

$plot->DrawGraph();

//---------------------------------------------------------------------

//PIE GRAPH------------------------------------------------------------
require_once 'phplot.php';

# The data labels aren't used directly by PHPlot. They are here for our
# reference, and we copy them to the legend below.
$data = array(
  array('Australia', 7849),
  array('Dem Rep Congo', 299),
  array('Canada', 5447),
  array('Columbia', 944),
  array('Ghana', 541),
  array('China', 3215),
  array('Philippines', 791),
  array('South Africa', 19454),
  array('Mexico', 311),
  array('United States', 9458),
  array('USSR', 9710),
);

$plot = new PHPlot(800,600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);

# Set enough different colors;
$plot->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan',
                        'magenta', 'brown', 'lavender', 'pink',
                        'gray', 'orange'));

# Main plot title:
$plot->SetTitle("World Gold Production, 1990\n(1000s of Troy Ounces)");

# Build a legend from our data array.
# Each call to SetLegend makes one line as "label: value".
foreach ($data as $row)
  $plot->SetLegend(implode(': ', $row));
# Place the legend in the upper left corner:
$plot->SetLegendPixels(5, 5);

$plot->DrawGraph();

//---------------------------------------------------------------------

//POINT ERROR GRAPH------------------------------------------------------------
require_once 'phplot.php';

$data = array(
  array('', 1,  23.5, 5, 5), array('', 2,  20.1, 3, 3),
  array('', 3,  19.1, 2, 2), array('', 4,  16.8, 3, 3),
  array('', 5,  18.4, 4, 6), array('', 6,  20.5, 3, 2),
  array('', 7,  23.2, 4, 4), array('', 8,  23.1, 5, 2),
  array('', 9,  24.5, 2, 2), array('', 10, 28.1, 2, 2),
);

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('points');
$plot->SetDataType('data-data-error');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Points Plot With Error Bars');

# Set data range and tick increments to get nice even numbers:
$plot->SetPlotAreaWorld(0, 0, 11, 40);
$plot->SetXTickIncrement(1);
$plot->SetYTickIncrement(5);

# Draw both grids:
$plot->SetDrawXGrid(True);
$plot->SetDrawYGrid(True);  # Is default

# Set some options for error bars:
$plot->SetErrorBarShape('tee');  # Is default
$plot->SetErrorBarSize(10);
$plot->SetErrorBarLineWidth(2);

# Use a darker color for the plot:
$plot->SetDataColors('brown');
$plot->SetErrorBarColors('brown');

# Make the points bigger so we can see them:
$plot->SetPointSizes(10);

$plot->DrawGraph();

//---------------------------------------------------------------------

//Scatterplot GRAPH 4 QUADRANT----------------------------------------------------
require_once 'phplot.php';

$data = array();
$a = 0.5;
$d_theta = M_PI/48.0;
for ($theta = M_PI * 7; $theta >= 0; $theta -= $d_theta)
  $data[] = array('', $a * $theta * cos($theta), $a * $theta * sin($theta));

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('points');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Scatterplot (points plot)');

# Need to set area and ticks to get reasonable choices.
$plot->SetPlotAreaWorld(-12, -12, 12, 12);
$plot->SetXTickIncrement(2);
$plot->SetYTickIncrement(2);

# Move axes and ticks to 0,0, but turn off tick labels:
$plot->SetXAxisPosition(0); # Is default
$plot->SetYAxisPosition(0);
$plot->SetXTickPos('xaxis');
$plot->SetXTickLabelPos('none');
$plot->SetYTickPos('yaxis');
$plot->SetYTickLabelPos('none');

# Turn on 4 sided borders, now that axes are inside:
$plot->SetPlotBorderType('full');

# Draw both grids:
$plot->SetDrawXGrid(True);
$plot->SetDrawYGrid(True);  # Is default

$plot->DrawGraph();
//---------------------------------------------------------------------

//XY GRAPH ------------------------------------------------------------
require_once 'phplot.php';

# To get repeatable results with 'random' data:
mt_srand(1);

# Make some noisy data:
$data = array();
for ($i = 0; $i < 100; $i++)
  $data[] = array('', $i / 4.0 + 2.0 + mt_rand(-20, 20) / 10.0);

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('squared');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

$plot->SetTitle('Noisy Data (squared plot)');

# Make the lines a bit wider:
$plot->SetLineWidths(2);

# Turn on the X grid (Y grid is on by default):
$plot->SetDrawXGrid(True);

# Use exactly this data range:
$plot->SetPlotAreaWorld(0, 0, 100, 40);

//---------------------------------------------------------------------

//STACKBAR GRAPH ------------------------------------------------------
require_once 'phplot.php';

$data = array(
  array('Jan', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4),
  array('Mar', 50, 6, 10, 4), array('Apr', 40, 3, 20, 4),
  array('May', 75, 2, 10, 5), array('Jun', 45, 6, 15, 5),
  array('Jul', 40, 5, 20, 6), array('Aug', 35, 6, 12, 6),
  array('Sep', 50, 5, 10, 7), array('Oct', 45, 6, 15, 8),
  array('Nov', 35, 6, 20, 9), array('Dec', 40, 7, 12, 9),
);

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

$plot->SetTitle('Candy Sales by Month and Product');
$plot->SetYTitle('Millions of Units');

# No shading:
$plot->SetShading(0);

$plot->SetLegend(array('Chocolates', 'Mints', 'Hard Candy', 'Sugar-Free'));
# Make legend lines go bottom to top, like the bar segments (PHPlot > 5.4.0)
$plot->SetLegendReverse(True);

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

$plot->DrawGraph();
//---------------------------------------------------------------------

//PRINT 2 graphs on top of each other----------------------------------
require_once 'phplot.php';

$data1 = array(        # Data array for top plot: Imports
  array('1981', 5996),  array('1982', 5113),  array('1983', 5051),
  array('1984', 5437),  array('1985', 5067),  array('1986', 6224),
  array('1987', 6678),  array('1988', 7402),  array('1989', 8061),
  array('1990', 8018),  array('1991', 7627),  array('1992', 7888),
  array('1993', 8620),  array('1994', 8996),  array('1995', 8835),
  array('1996', 9478),  array('1997', 10162), array('1998', 10708),
  array('1999', 10852), array('2000', 11459),
);
$data2 = array(        # Data array for bottom plot: Exports
  array('1981', 595),  array('1982', 815),  array('1983', 739),
  array('1984', 722),  array('1985', 781),  array('1986', 785),
  array('1987', 764),  array('1988', 815),  array('1989', 859),
  array('1990', 857),  array('1991', 1001), array('1992', 950),
  array('1993', 1003), array('1994', 942),  array('1995', 949),
  array('1996', 981),  array('1997', 1003), array('1998', 945),
  array('1999', 940),  array('2000', 1040),
);

$plot = new PHPlot(800,600);
$plot->SetImageBorderType('plain');

# Disable auto-output:
$plot->SetPrintImage(0);

# There is only one title: it is outside both plot areas.
$plot->SetTitle('US Petroleum Import/Export');

# Set up area for first plot:
$plot->SetPlotAreaPixels(80, 40, 740, 350);

# Do the first plot:
$plot->SetDataType('text-data');
$plot->SetDataValues($data1);
$plot->SetPlotAreaWorld(NULL, 0, NULL, 13000);
$plot->SetDataColors(array('blue'));
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetYTickIncrement(1000);
$plot->SetYTitle("IMPORTS\n1000 barrels/day");

$plot->SetPlotType('bars');
$plot->DrawGraph();

# Set up area for second plot:
$plot->SetPlotAreaPixels(80, 400, 740, 550);

# Do the second plot:
$plot->SetDataType('text-data');
$plot->SetDataValues($data2);
$plot->SetPlotAreaWorld(NULL, 0, NULL, 1300);
$plot->SetDataColors(array('green'));
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetYTickIncrement(200);
$plot->SetYTitle("EXPORTS\n1000 barrels/day");

$plot->SetPlotType('bars');
$plot->DrawGraph();

# Output the image now:
$plot->PrintImage();
//---------------------------------------------------------------------

//Horizontal BAR GRAPH---------------------------------------------------------------------
require_once 'phplot.php';

$data = array(
  array('San Francisco CA', 20.11),
  array('Reno NV', 7.5),
  array('Phoenix AZ', 8.3),
  array('New York NY', 49.7),
  array('New Orleans LA', 64.2),
  array('Miami FL', 52.3),
  array('Los Angeles CA', 13.2),
  array('Honolulu HI', 18.3),
  array('Helena MT', 11.3),
  array('Duluth MN', 31.0),
  array('Dodge City KS', 22.4),
  array('Denver CO', 15.8),
  array('Burlington VT', 36.1),
  array('Boston MA', 42.5),
  array('Barrow AL', 4.2),
);

$plot = new PHPlot(800, 800);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetTitle("Average Annual Precipitation (inches)\n"
              . "Selected U.S. Cities");
$plot->SetBackgroundColor('gray');
#  Set a tiled background image:
$plot->SetPlotAreaBgImage('images/drop.png', 'centeredtile');
#  Force the X axis range to start at 0:
$plot->SetPlotAreaWorld(0);
#  No ticks along Y axis, just bar labels:
$plot->SetYTickPos('none');
#  No ticks along X axis:
$plot->SetXTickPos('none');
#  No X axis labels. The data values labels are sufficient.
$plot->SetXTickLabelPos('none');
#  Turn on the data value labels:
$plot->SetXDataLabelPos('plotin');
#  No grid lines are needed:
$plot->SetDrawXGrid(FALSE);
#  Set the bar fill color:
$plot->SetDataColors('salmon');
#  Use less 3D shading on the bars:
$plot->SetShading(2);
$plot->SetDataValues($data);
$plot->SetDataType('text-data-yx');
$plot->SetPlotType('bars');
$plot->DrawGraph();
//---------------------------------------------------------------------

//OVERLAY PLOT---------------------------------------------------------
require_once 'phplot.php';

$title = '2009 Outbreak, Positive Tests';

# Note: Graph is based on the real thing, but the data is invented.
# Data for plot #1: stackedbars:
$y_title1 = 'Number of positive tests';
$data1 = array(
    array('1/09',  200,  200,  300),
    array('2/09',  300,  100,  700),
    array('3/09',  400,  200,  800),
    array('4/09',  500,  300, 1200),
    array('5/09',  400,  400, 2500),
    array('6/09',  500,  600, 3600),
    array('7/09',  400, 1200, 3300),
    array('8/09',  300, 1500, 2500),
    array('9/09',  200, 1900,  800),
    array('10/09', 100, 2000,  200),
    array('11/09', 100, 2500,  100),
    array('12/09', 100, 2700,  200),
);
$legend1 = array('Strain A', 'Strain B', 'Strain C');

# Data for plot #2: linepoints:
$y_title2 = 'Percent Positive';
$data2 = array(
    array('1/09',   5),
    array('2/09',  10),
    array('3/09',  15),
    array('4/09',  30),
    array('5/09',  40),
    array('6/09',  45),
    array('7/09',  47),
    array('8/09',  35),
    array('9/09',  25),
    array('10/09', 20),
    array('11/09', 25),
    array('12/09', 30),
);
$legend2 = array('% positive');

$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain'); // For presentation in the manual
$plot->SetPrintImage(False); // Defer output until the end
$plot->SetTitle($title);
$plot->SetPlotBgColor('gray');
$plot->SetLightGridColor('black'); // So grid stands out from background

# Plot 1
$plot->SetDrawPlotAreaBackground(True);
$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data1);
$plot->SetYTitle($y_title1);
# Set and position legend #1:
$plot->SetLegend($legend1);
$plot->SetLegendPixels(5, 30);
# Set margins to leave room for plot 2 Y title on the right.
$plot->SetMarginsPixels(120, 120);
# Specify Y range of these data sets:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 5000);
$plot->SetYTickIncrement(500);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
# Format Y tick labels as integers, with thousands separator:
$plot->SetYLabelType('data', 0);
$plot->DrawGraph();

# Plot 2
$plot->SetDrawPlotAreaBackground(False); // Cancel background
$plot->SetDrawYGrid(False); // Cancel grid, already drawn
$plot->SetPlotType('linepoints');
$plot->SetDataValues($data2);
# Set Y title for plot #2 and position it on the right side:
$plot->SetYTitle($y_title2, 'plotright');
# Set and position legend #2:
$plot->SetLegend($legend2);
$plot->SetLegendPixels(690, 30);
# Specify Y range of this data set:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 50);
$plot->SetYTickIncrement(10);
$plot->SetYTickPos('plotright');
$plot->SetYTickLabelPos('plotright');
$plot->SetDataColors('black');
# Format Y tick labels as integers with trailing percent sign:
$plot->SetYLabelType('data', 0, '', '%');
$plot->DrawGraph();

# Now output the graph with both plots:
$plot->PrintImage();
//---------------------------------------------------------------------
?>
