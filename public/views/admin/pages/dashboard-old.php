<?= $this->layout('admin/layouts/main', [
  'meta' => [
    'title' => 'Dashboard - Admin Area | LoLBoost.gg',
    'h1' => 'Dashboard',
    'description' => 'Overview and statistics of the website.',
  ]
]) ?>

<?php
$year = date('Y');
$monthly_rev = [
  'January' => 0,
  'February' => 0,
  'March' => 0,
  'April' => 0,
  'May' => 0,
  'June' => 0,
  'July' => 0,
  'August' => 0,
  'September' => 0,
  'October' => 0,
  'November' => 0,
  'December' => 0
];
$monthly_eur_revenue = db_run_query('SELECT
    MONTHNAME(created_at) AS month, created_at, SUM(amount) AS sum
    FROM transactions
    WHERE currency = "EUR" AND status != "failed" AND
    YEAR(created_at) = ' . $year . '
    AND created_at IS NOT NULL
    GROUP BY month ORDER BY created_at ASC');

$monthly_usd_revenue = db_run_query('SELECT
    MONTHNAME(created_at) AS month, created_at, SUM(amount) AS sum
    FROM transactions
    WHERE currency = "USD" AND status != "failed" AND
    YEAR(created_at) = ' . $year . '
    AND created_at IS NOT NULL
    GROUP BY month ORDER BY created_at ASC');

// convert monthly_usd_revenue to eur
$monthly_usd_revenue = array_map(function ($val) {
  $val['sum'] = $val['sum'] / get_exchange_rate();
}, $monthly_usd_revenue);

$monthly_revenue = array_merge($monthly_eur_revenue, $monthly_usd_revenue);

if (!empty($monthly_revenue)) {
  foreach ($monthly_revenue as $val) {
    if (is_array($val) && isset($val['month'], $val['sum'])) {
      $monthly_rev[$val['month']] = util_format_price_input($val['sum']);
    }
  }
  $monthly_revenue_val = array_values($monthly_rev);

  $monthly_revenue_val_sum = array_sum($monthly_revenue_val);

  $monthly_revenue_val_max = max($monthly_revenue_val ?? [0]);

  $monthly_rev_values = json_encode($monthly_revenue_val);
}

$weekly_ords = [
  'Sunday' => 0,
  'Monday' => 0,
  'Tuesday' => 0,
  'Wednesday' => 0,
  'Thursday' => 0,
  'Friday' => 0,
  'Saturday' => 0,
];

$weekly_orders = db_run_query('SELECT 
DAYNAME(paid_at) AS day, paid_at, COUNT(*) AS count 
FROM orders 
WHERE status != "UNKNOWN" AND status != "UNPAID" AND 
WEEK(paid_at) = WEEK(NOW()) 
AND paid_at IS NOT NULL 
AND YEAR(paid_at) = YEAR(NOW()) 
GROUP BY day ORDER BY paid_at ASC');

if (!empty($weekly_orders)) {
  foreach ($weekly_orders as $val) {
    $weekly_ords[$val['day']] = $val['count'];
  }
  $weekly_orders_val = array_values($weekly_ords);

  $weekly_orders_val_sum = array_sum($weekly_orders_val);

  $weekly_orders_val_max = max($weekly_orders_val ?? [0]);

  $weekly_orders_values = json_encode($weekly_orders_val);
}


$forms = db_get_rows('boost_forms', ['select' => 'id,name']);
// group forms by game
$monthly_form_chart = [];

// get monthly orders price for each form
foreach ($forms as $key => $form) {
  $monthly_form_chart[$key]['monthly_orders_price'] = db_run_query('SELECT
        MONTH(paid_at) AS month, paid_at, SUM(price) AS sum
        FROM orders
        WHERE status != "UNKNOWN" AND status != "UNPAID" 
        AND form_id = ' . $form['id'] . ' 
        AND YEAR(paid_at) = YEAR(NOW())
        AND paid_at IS NOT NULL
        GROUP BY month ORDER BY paid_at ASC');

  $monthly_form_chart[$key]['monthly_orders_price'] = array_column($monthly_form_chart[$key]['monthly_orders_price'], 'sum', 'month');
  $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
  foreach ($months as $month) {
    if (!isset($monthly_form_chart[$key]['monthly_orders_price'][$month])) {
      $monthly_form_chart[$key]['monthly_orders_price'][$month] = 0;
    } else {
      $monthly_form_chart[$key]['monthly_orders_price'][$month] = util_format_price_input($monthly_form_chart[$key]['monthly_orders_price'][$month]);
    }
  }

  ksort($monthly_form_chart[$key]['monthly_orders_price']);

  $monthly_form_chart[$key]['monthly_orders_price'] = array_values($monthly_form_chart[$key]['monthly_orders_price']);
  $monthly_form_chart[$key]['name'] = $form['name'];
}

?>
<div class="row gap-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-header-title">Monthly Revenue</h2>
      </div>

      <div class="card-body">
        <div class="row align-items-sm-center mb-4">
          <div class="col-sm mb-3 mb-sm-0">
            <div class="d-flex align-items-center">
              <span class="h1 mb-0">€<?= $monthly_revenue_val_sum ?> EUR</span>
            </div>
          </div>

          <div class="col-sm-auto">
            <!-- Legend Indicators -->
            <div class="row font-size-sm">
              <div class="col-auto">
                <span class="legend-indicator bg-primary"></span> Transactions
              </div>
            </div>
            <!-- End Legend Indicators -->
          </div>
        </div>
        <!-- Line Chart -->
        <div class="chartjs-custom" style="height: 18rem;">
          <canvas id="updatingLineChart" data-hs-chartjs-options='{
                  "type": "line",
                  "data": {
                     "labels": ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                     "datasets": [{
                      "backgroundColor": ["rgba(55, 125, 255, .5)", "rgba(255, 255, 255, .2)"],
                      "borderColor": "#377dff",
                      "borderWidth": 2,
                      "pointRadius": 0,
                      "hoverBorderColor": "#377dff",
                      "pointBackgroundColor": "#377dff",
                      "pointBorderColor": "#fff",
                      "pointHoverRadius": 0,
                      "tension": 0.4
                    },
                    {
                      "backgroundColor": ["rgba(0, 201, 219, .5)", "rgba(255, 255, 255, .2)"],
                      "borderColor": "#00c9db",
                      "borderWidth": 2,
                      "pointRadius": 0,
                      "hoverBorderColor": "#00c9db",
                      "pointBackgroundColor": "#00c9db",
                      "pointBorderColor": "#fff",
                      "pointHoverRadius": 0,
                      "tension": 0.4
                    }]
                  },
                  "options": {
                    "gradientPosition": {"y1": 200},
                     "scales": {
                        "y": {
                          "grid": {
                            "color": "#2F3235",
                            "drawBorder": false,
                            "zeroLineColor": "#e7eaf3"
                          },
                          "ticks": {
                            "min": 0,
                            "beginAtZero": true,
                            "stepSize": <?= round($monthly_revenue_val_max / 100) ?>,
                            "color": "#97a4af",
                            "font": {
                              "family": "Open Sans, sans-serif"
                            },
                            "padding": 10,
                            "postfix": "€"
                          }
                        },
                        "x": {
                          "grid": {
                            "display": false,
                            "drawBorder": false
                          },
                          "ticks": {
                            "color": "#97a4af",
                            "font": {
                              "size": 12,
                              "family": "Open Sans, sans-serif"
                            },
                            "padding": 5
                          }
                        }
                    },
                    "plugins": {
                      "tooltip": {
                        "prefix": "€",
                        "hasIndicator": true,
                        "mode": "index",
                        "intersect": false,
                        "lineMode": true,
                        "lineWithLineColor": "rgba(19, 33, 68, 0.075)"
                      }
                    },
                    "hover": {
                      "mode": "nearest",
                      "intersect": true
                    }
                  }
                }'>
          </canvas>
        </div>
        <!-- End Line Chart -->
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card h-100">
      <div class="card-header">
        <h2 class="card-header-title">Weekly Orders</h2>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-4">
          <span class="h1 mb-0">
            <?= $weekly_orders_val_sum ?> Orders
          </span>
        </div>

        <!-- Bar Chart -->
        <div class="chartjs-custom">
          <canvas id="weekly_orders" class="js-chart" style="height: 20rem;" data-hs-chartjs-options='{
                  "type": "bar",
                  "data": {
                    "labels": ["Sun","Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                    "datasets": [{
                      "data": <?= $weekly_orders_values ?>,
                      "backgroundColor": "#377dff",
                      "hoverBackgroundColor": "#377dff",
                      "borderColor": "#377dff",
                      "maxBarThickness": "10"
                    }]
                  },
                  "options": {
                    "scales": {
                      "y": {
                        "grid": {
                          "color": "#2F3235",
                          "drawBorder": false,
                          "zeroLineColor": "#e7eaf3"
                        },
                        "ticks": {
                          "beginAtZero": true,
                          "stepSize": <?= round($weekly_orders_val_max / 100) ?>,
                          "fontSize": 12,
                          "fontColor": "#97a4af",
                          "fontFamily": "Open Sans, sans-serif",
                          "padding": 10,
                          "postfix": " orders"
                        }
                      },
                      "x": {
                        "grid": {
                          "display": false,
                          "drawBorder": false
                        },
                        "ticks": {
                          "font": {
                            "size": 12,
                            "family": "Open Sans, sans-serif"
                          },
                          "color": "#97a4af",
                          "padding": 5
                        },
                        "categoryPercentage": 0.5
                      }
                    },
                    "cornerRadius": 2,
                    "plugins": {
                      "tooltip": {
                      "postfix": " orders",
                      "hasIndicator": true,
                      "mode": "index",
                      "intersect": false
                      }
                    },
                    "hover": {
                      "mode": "nearest",
                      "intersect": true
                    }
                  }
                }'></canvas>
        </div>
        <!-- End Bar Chart -->
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header">
        <h2 class="card-header-title">Monthly Forms Revenue</h2>
      </div>
      <div class="card-body">

        <!-- Bar Chart -->
        <div class="chartjs-custom">
          <canvas id="monthly_form_chart" class="js-chart" style="height: 20rem;" data-hs-chartjs-options='{
                  "type": "bar",
                  "data": {
                    "labels": ["Jan","Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    "datasets": [{
                      "backgroundColor": "#377dff",
                      "hoverBackgroundColor": "#377dff",
                      "borderColor": "#377dff",
                      "maxBarThickness": "10"
                    }]
                  },
                  "options": {
                    "scales": {
                      "y": {
                        "grid": {
                          "color": "#2F3235",
                          "drawBorder": false,
                          "zeroLineColor": "#e7eaf3"
                        },
                        "ticks": {
                          "beginAtZero": true,
                          "stepSize": <?= round($weekly_orders_val_max / 100) ?>,
                          "fontSize": 12,
                          "fontColor": "#97a4af",
                          "fontFamily": "Open Sans, sans-serif",
                          "padding": 10,
                          "prefix": "€"
                        }
                      },
                      "x": {
                        "grid": {
                          "display": false,
                          "drawBorder": false
                        },
                        "ticks": {
                          "font": {
                            "size": 12,
                            "family": "Open Sans, sans-serif"
                          },
                          "color": "#97a4af",
                          "padding": 5
                        },
                        "categoryPercentage": 0.5
                      }
                    },
                    "cornerRadius": 2,
                    "plugins": {
                      "tooltip": {
                      "postfix": "€",
                      "hasIndicator": true,
                      "mode": "index",
                      "intersect": false
                    }
                    },
                    "hover": {
                      "mode": "nearest",
                      "intersect": true
                    }
                  }
                }'></canvas>
        </div>
        <!-- End Bar Chart -->
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/chart.js/dist/chart.min.js"></script>
<script>
  (function () {
    // INITIALIZATION OF UPDATING CHARTJS
    // =======================================================
    const updatingChartDatasets = [
      [
        <?= $monthly_rev_values ?>,
        // [2700, 3800, 6000, 7700, 4000, 5000, 4900, 2900, 4200, 2700, 4200, 5000]
      ]
    ];



    HSCore.components.HSChartJS.init(document.querySelector('#updatingLineChart'), {
      data: {
        datasets: [{
          data: updatingChartDatasets[0][0]
        }]
      }
    });


    const form_colors = ['#2470e3', '#3a7ee6', '#508de9', '#669beb', '#7ca9ee', '#92b8f1', '#a7c6f4', '#bdd4f7'];

    HSCore.components.HSChartJS.init(document.querySelector('#monthly_form_chart'), {
      data: {
        datasets: [<?php foreach ($monthly_form_chart as $key => $form): ?> {
            label: '<?= $form['name'] ?>',
            data: <?= json_encode($form['monthly_orders_price']) ?>,
            backgroundColor: form_colors[<?= $key ?>],
          },
          <?php endforeach ?>
        ]
      }
    });

    HSCore.components.HSChartJS.init(document.querySelector('#weekly_orders'));

    const updatingLineChart = HSCore.components.HSChartJS.getItem('updatingLineChart');
  })()
</script>
<?= $this->end() ?>