<?php

namespace widget;

use app\common\lib\Widget;
use app\common\logic\IndexLogic;

class ThisYearSaleDetail extends Widget
{
  protected $title = '本年销售详情';

  protected $weight = 70;

  protected $columns = 2;

  public function getData()
  {
    $data = (new IndexLogic())->thisYearSale();
    return $data;
  }

  public function output()
  {
    $content = '';
    $data = $this->getData();

    $xMonthList = [];
    $yAmountList = [];
    foreach ($data['this_year_month_amount'] as $value) {
      $xMonthList[] = $value['month'] . '月';
      $yAmountList[] = $value['amount'];
    }
    $xMonthList = json_encode($xMonthList);
    $yAmountList = json_encode($yAmountList);
    $currencySuffix = configuration('currency_suffix');
    if (!empty($currencySuffix)) {
      $currencySuffix = '（' . $currencySuffix . '）';
    }

    return <<<HTML
     <div id="echars-box" class="echars-div"></div>

    <script>
      let XmonthList = JSON.parse('{$xMonthList}'), YamountList = JSON.parse('{$yAmountList}')
      // res.data.data.this_year_month_amount.forEach(item => {
      //   XmonthList.push(item.month + '月')
      //   YamountList.push(item.amount)
      // });
      let el = document.getElementById('echars-box')
      let myChart = echarts.init(el);
      const option = {
        title: {
          text: "本年销售详情{$currencySuffix}",
          left: 30,
          top: 26,
          textStyle: {
            fontSize: 18,
            fontWeight: 'bold',
            color: 'rgba(0, 0, 0, 0.9)'
          }
        },
        tooltip: {},
        color: '#0052D9',
        xAxis: {
          type: 'category',
          data: XmonthList
        },
        yAxis: {
          type: 'value'
        },
        series: [{
          data: YamountList,
          type: 'bar'
        }]
      }
      myChart.setOption(option)
      window.addEventListener("resize", function () {
        myChart.resize()
      });
    </script>

HTML;
  }
}
