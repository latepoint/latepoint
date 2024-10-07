/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */


function latepoint_init_daily_bookings_chart() {
  if (typeof Chart === 'undefined' || !jQuery('#chartDailyBookings').length) return

  let $dailyBookingsChart = jQuery('#chartDailyBookings');
  let dailyBookingsLabels = $dailyBookingsChart.data('chart-labels').toString().split(',');
  let dailyBookingsValues = $dailyBookingsChart.data('chart-values').toString().split(',').map(Number);
  let dailyBookingsChartMax = Math.max.apply(Math, dailyBookingsValues);
  // calculate max Y to have space for a tooltip
  let canvasHeight = 200
  let spaceForTooltip = 160
  let maxValue = dailyBookingsChartMax + spaceForTooltip * dailyBookingsChartMax / canvasHeight + 1


  var fontFamily = latepoint_helper.body_font_family;

  Chart.Tooltip.positioners.top = function (items) {
    const pos = Chart.Tooltip.positioners.average(items);

    // Happens when nothing is found
    if (pos === false) {
      return false;
    }

    const chart = this.chart;

    return {
      x: pos.x,
      y: chart.chartArea.top,
      xAlign: 'center',
      yAlign: 'bottom',
    };
  };

  Chart.defaults.defaultFontFamily = fontFamily;
  Chart.defaults.defaultFontSize = 18;
  Chart.defaults.defaultFontStyle = '400';
  Chart.defaults.plugins.tooltip.titleFont = {
    family: fontFamily,
    size: 14,
    color: 'rgba(255,255,255,0.6)',
    style: 'normal',
    weight: 400
  }

  Chart.defaults.plugins.tooltip.titleFont = {family: fontFamily, size: 14, weight: 400};
  Chart.defaults.plugins.tooltip.titleColor = 'rgba(255,255,255,0.6)';
  Chart.defaults.plugins.tooltip.backgroundColor = '#000';
  Chart.defaults.plugins.tooltip.titleMarginBottom = 5;
  Chart.defaults.plugins.tooltip.bodyFont = {family: fontFamily, size: 24, weight: 700, lineHeight: 0.8};
  Chart.defaults.plugins.tooltip.displayColors = false;
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.yAlign = 'bottom';
  Chart.defaults.plugins.tooltip.xAlign = 'center';
  Chart.defaults.plugins.tooltip.cornerRadius = 4;
  Chart.defaults.plugins.tooltip.caretSize = 5;
  Chart.defaults.plugins.tooltip.position = 'top';

  var ctx = $dailyBookingsChart[0].getContext("2d");
  var gradientStroke = ctx.createLinearGradient(500, 0, 100, 0);
  gradientStroke.addColorStop(0, '#1d7bff');
  gradientStroke.addColorStop(1, '#1d7bff');


  let gradientFill = ctx.createLinearGradient(0, 0, 0, 140);
  gradientFill.addColorStop(0, 'rgb(206,224,255, 0.4)');
  gradientFill.addColorStop(1, 'rgba(206,224,255,0)');

  // line chart data
  var chartDailyBookingsData = {
    labels: dailyBookingsLabels,
    datasets: [{
      backgroundColor: gradientFill,
      borderColor: gradientStroke,
      label: "",
      fill: true,
      lineTension: 0.1,
      borderWidth: 2,
      borderCapStyle: 'butt',
      borderDash: [],
      borderDashOffset: 0.0,
      borderJoinStyle: 'miter',
      pointBorderColor: "#fff",
      pointBackgroundColor: "#1D7BFF",
      pointRadius: 3,
      pointBorderWidth: 2,
      pointHoverRadius: 6,
      pointHoverBorderWidth: 4,
      pointHoverBackgroundColor: "#1D7BFF",
      pointHoverBorderColor: "#aecdff",
      pointHitRadius: 20,
      spanGaps: false,
      data: dailyBookingsValues,
    }]
  };


  let options = {
    animation: false,
    layout: {
      padding: {
        top: 0
      }
    },
    interaction: {
      mode: 'index',
      intersect: false,
    },
    maintainAspectRatio: false,
    plugins: {
      verticalLiner: {},
      legend: {
        display: false
      },
    },
    scales: {
      x: {
        display: true,
        ticks: {
          fontFamily: fontFamily,
          maxRotation: 0,
          color: '#1f222b',
          font: {
            size: 11,
            family: fontFamily
          },
          callback: function (value, index, ticks) {
            if(ticks.length){
              return ((index + 2) % Math.round(ticks.length/8)) ? '' : this.getLabelForValue(value)
            }else{
              return this.getLabelForValue(value)
            }
          }
        },
        grid: {
          borderDash: [1, 5],
          color: 'rgba(0,0,0,0.35)',
          zeroLineColor: 'rgba(0,0,0,0.15)',
        },
      },
      y: {
        max: maxValue,
        grid: {
          color: 'rgba(0,0,0,0.05)',
          zeroLineColor: 'rgba(0,0,0,0.05)',
        },
        display: false,
        ticks: {
          beginAtZero: true,
          fontSize: '10',
          fontColor: '#000'
        }
      }

    }
  }

  const plugin = {
    id: 'verticalLiner',
    afterInit: (chart, args, opts) => {
      chart.verticalLiner = {}
    },
    afterEvent: (chart, args, options) => {
      const {inChartArea} = args
      chart.verticalLiner = {draw: inChartArea}
    },
    beforeTooltipDraw: (chart, args, options) => {
      const {draw} = chart.verticalLiner
      if (!draw) return

      const {ctx} = chart
      const {top, bottom} = chart.chartArea
      const {tooltip} = args
      const x = tooltip.caretX
      if (!x) return

      ctx.save()

      ctx.beginPath()
      ctx.moveTo(x, top)
      ctx.lineTo(x, bottom)
      ctx.stroke()

      ctx.restore()
    }
  }

  // line chart init
  let chartDailyBookings = new Chart($dailyBookingsChart, {
    type: 'line',
    data: chartDailyBookingsData,
    options: options,
    plugins: [plugin],
  });
}


function latepoint_init_customer_donut_chart() {
  if (typeof Chart !== 'undefined' && jQuery('.os-customer-donut-chart').length) {
    var fontFamily = latepoint_helper.body_font_family;
    // set defaults
    Chart.defaults.defaultFontFamily = fontFamily;
    Chart.defaults.defaultFontSize = 16;
    Chart.defaults.defaultFontStyle = '400';

    Chart.defaults.plugins.tooltip.titleFont = {family: fontFamily, size: 14, weight: 400};
    Chart.defaults.plugins.tooltip.titleColor = 'rgba(255,255,255,0.6)';
    Chart.defaults.plugins.tooltip.backgroundColor = '#000';
    Chart.defaults.plugins.tooltip.titleMarginBottom = 1;
    Chart.defaults.plugins.tooltip.bodyFont = {family: fontFamily, size: 18, weight: 500};
    Chart.defaults.plugins.tooltip.displayColors = false;
    Chart.defaults.plugins.tooltip.padding = 5;
    Chart.defaults.plugins.tooltip.yAlign = 'bottom';
    Chart.defaults.plugins.tooltip.xAlign = 'center';
    Chart.defaults.plugins.tooltip.cornerRadius = 4;
    Chart.defaults.plugins.tooltip.intersect = false;
    jQuery('.os-customer-donut-chart').each(function (index) {
      var chart_colors = jQuery(this).data('chart-colors').toString().split(',');
      var chart_labels = jQuery(this).data('chart-labels').toString().split(',');
      var chart_values = jQuery(this).data('chart-values').toString().split(',').map(Number);
      var $chart_canvas = jQuery(this);
      var chartDonut = new Chart($chart_canvas, {
        type: 'doughnut',
        data: {
          labels: chart_labels,
          datasets: [{
            data: chart_values,
            backgroundColor: chart_colors,
            hoverBackgroundColor: chart_colors,
            borderWidth: 0,
            hoverBorderColor: 'transparent'
          }]
        },
        options: {
          layout: {
            padding: {
              top: 10,
              bottom: 10,
              left: 10,
              right: 10
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                title: function (tooltipItem) {
                  return tooltipItem[0].label;
                },
                label: function (tooltipItem) {
                  return tooltipItem.parsed;
                },
              }
            },
          },
          animation: {
            animateRotate: false
          },
          cutout: "90%",
          responsive: false,
          maintainAspectRatio: true,
        }
      });
    });
  }
}

function latepoint_init_donut_charts() {
  if (typeof Chart !== 'undefined' && jQuery('.os-donut-chart').length) {
    var fontFamily = latepoint_helper.body_font_family;
    // set defaults
    Chart.defaults.defaultFontFamily = fontFamily;
    Chart.defaults.defaultFontSize = 18;
    Chart.defaults.defaultFontStyle = '400';

    Chart.defaults.plugins.tooltip.titleFont.family = fontFamily;
    Chart.defaults.plugins.tooltip.titleFont.size = 14;
    Chart.defaults.plugins.tooltip.titleColor = 'rgba(255,255,255,0.6)';
    Chart.defaults.plugins.tooltip.backgroundColor = '#000';
    Chart.defaults.plugins.tooltip.titleFont.style = '400';
    Chart.defaults.plugins.tooltip.titleMarginBottom = 1;
    Chart.defaults.plugins.tooltip.bodyFont.family = fontFamily;
    Chart.defaults.plugins.tooltip.bodyFont.size = 24;
    Chart.defaults.plugins.tooltip.bodyFont.style = '500';
    Chart.defaults.plugins.tooltip.displayColors = false;
    Chart.defaults.plugins.tooltip.padding.x = 10;
    Chart.defaults.plugins.tooltip.padding.y = 8;
    Chart.defaults.plugins.tooltip.yAlign = 'bottom';
    Chart.defaults.plugins.tooltip.xAlign = 'center';
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.intersect = false;
    jQuery('.os-donut-chart').each(function (index) {
      var chart_colors = jQuery(this).data('chart-colors').toString().split(',');
      var chart_labels = jQuery(this).data('chart-labels').toString().split(',');
      var chart_values = jQuery(this).data('chart-values').toString().split(',').map(Number);
      var $chart_canvas = jQuery(this);
      var chartDonut = new Chart($chart_canvas, {
        type: 'doughnut',
        data: {
          labels: chart_labels,
          datasets: [{
            data: chart_values,
            backgroundColor: chart_colors,
            hoverBackgroundColor: chart_colors,
            borderWidth: 0,
            hoverBorderColor: 'transparent'
          }]
        },
        options: {
          layout: {
            padding: {
              top: 40
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                title: function (tooltipItem, data) {
                  return data['labels'][tooltipItem[0]['index']];
                },
                label: function (tooltipItem, data) {
                  return data['datasets'][0]['data'][tooltipItem['index']];
                }
              }
            }
          },
          animation: {
            animateScale: true
          },
          cutoutPercentage: 96,
          responsive: false,
          maintainAspectRatio: true,
        }
      });
    });
  }
}


function latepoint_init_circles_charts() {
  jQuery('.circle-chart').each(function (index) {
    var chart_elem_id = jQuery(this).prop('id');
    var max_value = jQuery(this).data('max-value');
    var chart_value = jQuery(this).data('chart-value');
    var chart_color = jQuery(this).data('chart-color');
    var chart_color_fade = jQuery(this).data('chart-color-fade');
    var myCircle = Circles.create({
      id: chart_elem_id,
      radius: 25,
      value: chart_value,
      maxValue: max_value,
      width: 2,
      text: function (value) {
        return Math.round(value);
      },
      colors: [chart_color, chart_color_fade],
      duration: 200,
      wrpClass: 'circles-wrp',
      textClass: 'circles-text',
      valueStrokeClass: 'circles-valueStroke',
      maxValueStrokeClass: 'circles-maxValueStroke',
      styleWrapper: true,
      styleText: true
    });

  });


}
