<?php

require_once(module('stat'));

class TAttendance extends TStat {

    var $name = 'stat/stat_attendance';
    var $events = array();
    var $colors = array('brown', '#FF59A7', '#9900FF', 'green', 'aqua', 'black', 'blue', 'gray', '#C092F8', 'red', 'yellow', 'orange', 'maroon');

    ########################

    function TAttendance() {
        global $str, $actions;

        TStat::TStat();

        $this->page_id = get('page_id')?(int)get('page_id'):get('page_id',0,'p');
        $analyze_page = get('analyze_page');
        $parsed_url = parse_url($analyze_page);

        if($analyze_page) {
            if(isset($parsed_url['host'])) {
                if(!isset($parsed_url['path'])) {
                    $parsed_url['path'] = '/';
                }
                $this->page_id = sql_getValue('SELECT id FROM '.STAT_PAGES_TABLE.' WHERE host="'.str_replace('www.','',$parsed_url['host']).'" AND uri="'.$parsed_url['path'].'"');
            }
            else {
                $temp = explode("/",$analyze_page);
                if(isset($temp['1'])) {
                    $uri = str_replace($temp['0'],'',$analyze_page);
                }
                else {
                    $uri = '/'.$analyze_page;
                }
                $this->page_id = sql_getValue('SELECT id FROM '.STAT_PAGES_TABLE.' WHERE uri="'.$uri.'"');
            }
            $this->analyze_page = $analyze_page;
        }
        else {
            if(!empty($row)) {
                $this->analyze_page = 'http://'.$row['host'].$row['uri'];
            }
        }

        if(!$this->page_id) $this->page_id = 0;

        if (STAT_EVENT_REPORT == true) {
            $row = sql_getRow('SELECT * FROM '.STAT_SETTINGS_TABLE.' WHERE name="events"', true);
            if (!empty($row['value'])) {
                $events = unserialize($row['value']);
                foreach ($events as $key=>$val) {
                    $this->events[] = array(
                    'name' => $key,
                    'url'  => $val,
                    );
                }
            }
        }

        $actions[str_replace('/', '__', $this->name)] = array(
        'view_table'	=> &$actions['tstat']['view_table'],
        'view_bargrapf'	=> &$actions['tstat']['view_bargrapf'],
        'view_grapf'	=> &$actions['tstat']['view_grapf'],
        'view_csv'		=> &$actions['tstat']['view_csv'],
        );

        $str[get_class_name($this)] = $str['tstat'] + array(
        'visitors'	=> array(
        'ѕосетители',
        'Visitors',
        ),
        'no_reklama'	=> array(
        '»з них без рекламы',
        'Without reklama',
        ),
        'clients'	=> array(
        '–ег. клиенты',
        'Reg. clients',
        ),
        'pages'	=> array(
        'ѕросмотренные страницы',
        'Pages viewed',
        ),
        'new_visitors'	=> array(
        'Ќовые посетители',
        'New visitors',
        ),
        'ips'	=> array(
        'IP-адреса',
        'IP-addresses',
        ),
        'for_page'	=> array(
        'јнализируема€ страница',
        'Analyzing page',
        ),
        'from_to'	=> array(
        'c %s по %s',
        'from %s to %s',
        ),
        'for_day'	=> array(
        'за %s числа мес€ца',
        'for %s day of month',
        ),
        'during'	=> array(
        'за %s',
        'during %s',
        ),
        'on_average' => array(
        '¬ среднем',
        'On average',
        ),
        'page_id' => array(
        '# страницы',
        '# of page',
        ),
        ######## Navig Form ###########
        'nav_interval' 	=> array(
        'интервал',
        'interval',
        ),
        'nav_by_hour' 	=> array(
        'по часам',
        'by hours',
        ),
        'nav_by_days' 	=> array(
        'по дн€м',
        'by days',
        ),
        'nav_by_weeks' 	=> array(
        'по недел€м',
        'by weeks',
        ),
        'nav_by_month' 	=> array(
        'по мес€цам',
        'by months',
        ),
        'nav_sum' 	=> array(
        'суммировать по интервалу',
        'sum by interval',
        ),
        'nav_show_visit' 	=> array(
        'показывать посетителей',
        'show visitors',
        ),
        'nav_show_no_reklama' 	=> array(
        'показывать посетителей без рекламы',
        'show visitors without reklama',
        ),
        'nav_show_clients' 	=> array(
        'показывать рег. пользоваетелей',
        'show reg. clients',
        ),
        'nav_show_loads' 	=> array(
        'показывать просмотренных страниц',
        'show viewed pages',
        ),
        'nav_show_uvisit' 	=> array(
        'показывать новых посетителей',
        'show new visitors',
        ),
        'nav_show_hosts' 	=> array(
        'показывать ip-адреса',
        'show ip-addresses',
        ),
        );

        // set special values
        $this->disp_by		=      get('disp_by', 		$this->Param('disp_by', 'dayofmonth'));
        $this->group		= (int)get('group', 		$this->Param('group', '0'));
        $this->show_visit	= (int)get('show_visit',	$this->Param('show_visit',	'1'));
        $this->show_no_reklama	= (int)get('show_no_reklama',	$this->Param('show_no_reklama',	'1'));
        $this->show_clients	= (int)get('show_clients',	$this->Param('show_clients',	'0'));
        $this->show_uvisit	= (int)get('show_uvisit',	$this->Param('show_uvisit',	'1'));
        $this->show_hosts	= (int)get('show_hosts',	$this->Param('show_hosts',	'1'));
        $this->show_loads	= (int)get('show_loads',	$this->Param('show_loads',	'0'));
        if ($this->events) foreach ($this->events as $key=>$val) {
            $temp = 'show_event_'.$key;
            $this->$temp = (int)get($temp,    $this->Param($temp,    '0'));
        }
        // save params
        $this->Param('group',		'', $this->group);
        $this->Param('disp_by',		'', $this->disp_by);
        $this->Param('show_visit',	'', $this->show_visit);
        $this->Param('show_no_reklama',	'', $this->show_no_reklama);
        $this->Param('show_clients','', $this->show_clients);
        $this->Param('show_uvisit',	'', $this->show_uvisit);
        $this->Param('show_hosts',	'', $this->show_hosts);
        $this->Param('show_loads',	'', $this->show_loads);
    }

    ######################

    function Show() {
        $this->Init();
        $this->AddStrings($ret);
        if ($this->show!='bargraph' &&
        $this->show!='graph' &&
        $this->show!='csv')
        $this->show = 'table';
        $ret = @call_user_func(array(&$this, 'Get'.$this->show));

        $ret['navig'] = $this->NavigForm(array(
        'interval'	=> true,
        'attendance'	=> ($this->show=='bargraph' || $this->show=='graph' ? true : false),
        'hidden'	=> array('show' => $this->show),
        'show_analyze_page' => 'show',
        ));
        $ret['site_select'] = $this->selectSite(array(
        'hidden'	=> array('show' => $this->show),
        ));

        if(isset($this->analyze_page)) {
            $ret['analyze_page'] = $this->analyze_page;
        }
        else {
            $ret['analyze_page'] = '';
        }
        return Parse($ret, 'stat/stat.tmpl');
    }

    ######################

    function GetTable() {
        $data = $this->GetData();
        $j = 0;
        $total_head[$j] = '';
        if ($this->page_id) $total_head[++$j] = $this->_str('for_page');

        //////////////////////////////////////////
        $total_head[++$j] = $this->_str('visitors');
        if (STAT_REKLAMA_REPORT) $total_head[++$j] = $this->_str('no_reklama');
        if (STAT_CLIENT_REPORT) $total_head[++$j] = $this->_str('clients');
        $total_head = array_merge($total_head, array(
        $this->_str('pages'),
        $this->_str('new_visitors'),
        $this->_str('ips'),
        )
        );
        $j += 3;
        if ($this->events) foreach ($this->events as $key=>$val) {
            $total_head[++$j] = $val['name'];
        }

        // строка всего
        $j = 0;
        $total[0][$j] = $this->str('total_period');
        if ($this->page_id) $total[0][++$j] = (int)sql_getValue("SELECT COUNT(*) FROM ".STAT_LOG_TABLE." AS log LEFT JOIN ".$this->sess_table." AS sess ON log.sess_id = sess.sess_id WHERE sess.robot=0 AND log.page_id=".$this->page_id." group by log.page_id");
        // строка в среднем
        $total[1][0] = $this->str('on_average');
        if ($this->page_id) $total[1][$j] = round($total[0][$j]/$data['count'], 1);

        $total[0][++$j] = (int)sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0");
        if (STAT_REKLAMA_REPORT) {
            $total[0][++$j] = (int)sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." AS s, ".STAT_PAGES_TABLE." AS p WHERE s.first_page = p.id AND s.robot=0 AND p.uri NOT LIKE '%?from=%'");
            $total[0][$j] .= '&nbsp;<span style="font-size: 80%">('.round($total[0][$j]/$total[0][$j-1]*100,2).'%)</span>';
        }
        if (STAT_CLIENT_REPORT) $total[0][++$j] = (int)sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND client_id!=0");

        $total[0] = array_merge($total[0], array(
        (int)sql_getValue("SELECT SUM(loads) FROM ".$this->sess_table." WHERE robot=0"),
        (int)sql_getValue("SELECT COUNT(*) FROM ".$this->sess_table." WHERE robot=0 AND new_visitor='1'"),
        (int)sql_getValue("SELECT COUNT(DISTINCT(ip)) FROM ".$this->sess_table." WHERE robot=0"),
        )
        );

        foreach ($total[0] as $key=>$val) {
            if ($key==0) continue;
            $total[1][$key] = round($total[0][$key]/$data['count'], 1);
        }
        if ($this->events) {
            foreach ($this->events as $key=>$val) {
                $i = count($total[0]);
                $total[0][$i] = (int)sql_getValue("SELECT COUNT(pages.uri) AS count FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_LOG_TABLE." AS log ON sess.sess_id=log.sess_id LEFT JOIN ".STAT_PAGES_TABLE." AS pages ON log.page_id=pages.id WHERE robot='0' AND pages.uri LIKE '".str_replace("*","%",$val['url'])."'");
                $total[0][$i] .= '&nbsp;<span style="font-size: 80%">('.round($total[0][$i]/$total[0][1]*100,2).'%)</span>';
                $total[1][$i] = round($total[0][$i]/$data['count'], 1);
            }
        }

        // ѕодготавливает данные дл€ таблицы
        foreach ($data['rows'] as $i => $row) {
            $j = 0;
            $sdata[$i][$j] = $data['str'][$i];
            if (isset($data['for_page'])) $sdata[$i][++$j] = (int)$data['for_page'][$i];
            $sdata[$i][++$j] = isset($data['visit'][$i]) ? (int)$data['visit'][$i] : 0;
            if (STAT_REKLAMA_REPORT) {
                $sdata[$i][++$j] = isset($data['no_reklama'][$i]) ? (int)$data['no_reklama'][$i] : 0;
                $sdata[$i][$j] .= "&nbsp;<span style='font-size: 80%; color: #666666;'>(".round(($sdata[$i][$j]/$sdata[$i][$j-1]*100),2)."%)</span>";
            }
            if (STAT_CLIENT_REPORT) $sdata[$i][++$j] = isset($data['clients'][$i]) ? (int)$data['clients'][$i] : 0;
            $sdata[$i] = array_merge($sdata[$i], array(
            isset($data['loads'][$i])    ? (int)$data['loads'][$i]    : 0,
            isset($data['uvisit'][$i])    ? (int)$data['uvisit'][$i]    : 0,
            isset($data['hosts'][$i])    ? (int)$data['hosts'][$i]    : 0,
            )
            );
            if($this->events){
                foreach ($this->events as $key=>$val) {
                    $num = isset($data['event_'.$key][$i]) ? (int)$data['event_'.$key][$i] : 0;
                    $sdata[$i][] = $num."&nbsp;<span style='font-size: 80%; color: #666666;'>(".round(($num/$data['visit'][$i]*100),2)."%)</span>";
                }
            }
        }

        // Main Table
        $colums = array();
        $colums[] = array(
        'header'     => '',
        'nowrap'    => 1,
        );
        if($this->page_id) {
            $colums[] = array(
            'header'     => 'for_page',
            'align'        => 'right',
            'width'        => '10%',
            );
        }
        $colums[] =    array(
        'header'     => 'visitors',
        'align'        => 'right',
        'width'        => '10%',
        );
        if(STAT_REKLAMA_REPORT) {
            $colums[] =    array(
            'header'     => 'no_reklama',
            'align'        => 'right',
            'width'        => '10%',
            );
        }
        if(STAT_CLIENT_REPORT) {
            $colums[] =array(
            'header'     => 'clients',
            'align'        => 'right',
            'width'        => '10%',
            );
        }
        $colums[] =    array(
        'header'     => 'pages',
        'align'        => 'right',
        'width'        => '10%',
        );
        $colums[] = array(
        'header'     => 'new_visitors',
        'align'        => 'right',
        'width'        => '10%',
        );
        $colums[] =array(
        'header'     => 'ips',
        'align'        => 'right',
        'width'        => '10%',
        );

        if($this->events){
            foreach ($this->events as $key=>$val) {
                $colums[] = array(
                'header'     => $val['name'],
                'align'        => 'right',
                'width'        => '10%',
                );
            }
        }

        $ret['table'] = $this->stat_table(array(
        'columns'	=> $colums,
        'data' => $sdata,
        'total' => $total,
        'total_head' => $total_head,
        'count' => $data['count'],
        'offset' => $data['offset'],
        'limit' => $data['limit'],
        ));
        return $ret;
    }

    ######################

    function GetCSVData() {
        $_GET['offset'] = 0;
        $_GET['limit'] = -1;
        $data = $this->GetData();

        echo
        ';'.
        $this->_str('visitors').';'.
        $this->_str('no_reklama').';'.
        $this->_str('clients').';'.
        $this->_str('pages').';'.
        $this->_str('new_visitors').';'.
        $this->_str('ips');
        if (!empty($this->events)) {
            echo ";";
            foreach ($this->events as $key=>$val) {
                echo $val['name'].";";
            }
        }
        echo "\n";

        if ($data['rows'])
        foreach ($data['rows'] as $i=>$row) {
            $string = $row.';'.
            (isset($data['visit'][$i])	? (int)$data['visit'][$i]	: 0).';'.
            (isset($data['no_reklama'][$i])	? (int)$data['no_reklama'][$i]	: 0).';'.
            (isset($data['clients'][$i])? (int)$data['clients'][$i]	: 0).';'.
            (isset($data['loads'][$i])	? (int)$data['loads'][$i]	: 0).';'.
            (isset($data['uvisit'][$i])	? (int)$data['uvisit'][$i]	: 0).';'.
            (isset($data['hosts'][$i])	? (int)$data['hosts'][$i]	: 0);
            if (!empty($this->events)) {
                $string .= ";";
                foreach ($this->events as $key=>$val) {
                    $string .= (isset($data['event_'.$key][$i])    ? (int)$data['event_'.$key][$i]    : 0).';';
                }
            }
            $string .= "\n";
            echo $string;
        }
    }

    ######################

    function GetBarGraph() {
        return $this->GetGraph();
    }

    function GetGraph() {
        $img_params = array(
        'page'		=> $this->name,
        'do'		=> 'Show'.$this->show.'Img',
        'from_date'	=> date('Y-m-d', $this->from_date),
        'to_date'	=> date('Y-m-d', $this->to_date),
        'filter'	=> get('filter', 0),
        'disp_by'	=> $this->disp_by,
        'group'		=> $this->group,
        'show_visit'	=> $this->show_visit ? 1 : 0,
        'show_no_reklama'	=> $this->show_no_reklama ? 1 : 0,
        'show_clients'	=> $this->show_clients ? 1 : 0,
        'show_uvisit'	=> $this->show_uvisit ? 1 : 0,
        'show_hosts'	=> $this->show_hosts ? 1 : 0,
        'show_loads'	=> $this->show_loads ? 1 : 0,
        'page_id' => $this->page_id,
        'site'       => $this->site
        );
        if ($this->events) foreach ($this->events as $key=>$val) {
            $temp = 'show_event_'.$key;
            $img_params[$temp] = $this->$temp ? 1 : 0;
        }

        // ƒополнительные параметры
        if (isset($this->advanced)) {
            $img_params['adv['.$this->advanced['key'].']'] = $this->advanced['value'];
        }

        $ret['image'] = array(
        'src' => 'page.php?'.$this->query_str($img_params),
        'alt' => $this->GetTitle(),
        );

        return $ret;
    }

    ######################

    function toColor($n)
    {
        if ($n > 1048576) {
            return("#".dechex($n));
        }
        else return("#".substr("000000".dechex($n),-6));
    }

    function ShowGraphImg() {
        $this->Init();

        $_GET['offset'] = 0;
        $_GET['limit'] = -1;
        $data = $this->GetData();

        $y = 8;
        if ($this->events) foreach ($this->events as $key=>$val) {
            $temp = 'ydata'.$y;
            $$temp = array();
            $y++;
        }

        foreach ($data['rows'] as $i => $row) {
            $xdata[] = $row;
            $ydata1[] = isset($data['visit'][$i])	? (int)$data['visit'][$i]	: 0;
            $ydata2[] = isset($data['no_reklama'][$i])	? (int)$data['no_reklama'][$i]	: 0;
            $ydata3[] = isset($data['clients'][$i])	? (int)$data['clients'][$i]	: 0;
            $ydata4[] = isset($data['uvisit'][$i])	? (int)$data['uvisit'][$i]	: 0;
            $ydata5[] = isset($data['hosts'][$i])	? (int)$data['hosts'][$i]	: 0;
            $ydata6[] = isset($data['loads'][$i])	? (int)$data['loads'][$i]	: 0;
            $ydata7[] = isset($data['for_page'][$i])	? (int)$data['for_page'][$i]	: 0;

            $y = 8;
            if ($this->events) foreach ($this->events as $key=>$val) {
                $temp = 'ydata'.$y;
                array_push($$temp, isset($data['event_'.$key][$i])    ? (int)$data['event_'.$key][$i]    : 0);
                $y++;
            }
        }

        $graph_width = $data['count'] * 20;
        if ($graph_width < $this->graph_width)
        $graph_width = $this->graph_width;

        include ("jpgraph/jpgraph.php");
        include ("jpgraph/jpgraph_line.php");

        // Create the graph and specify the scale for both Y-axis
        $graph = new Graph($graph_width, $this->graph_height, 'auto');
        $graph->SetScale("textlin");
        $graph->SetShadow();
        $graph->SetAlphaBlending();

        // Adjust the margin
        $graph->img->SetMargin(40,40,20,200);

        // Setup scale
        $graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
        $graph->xaxis->SetLabelMargin(10);
        $graph->xaxis->SetTickLabels($xdata);
        $graph->xaxis->SetLabelAngle(90);
        $graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,7);

        // Titles
        $graph->title->SetFont(FF_VERDANA,FS_BOLD);
        $graph->title->Set($this->GetTitle());

        //        $color = 255;
        //        $dcolor = 255*100;
        if ($this->show_visit) {
            // Create linear plot
            $lineplot1 = new LinePlot($ydata1);
            // Set the color for the plot
            $lineplot1->SetColor($this->colors[0]);
            //            $lineplot1->SetColor($this->toColor($color));
            $lineplot1->SetWeight(2);
            // Set the legend for the plot
            $lineplot1->SetLegend($this->str('visitors'));
            // Add the plot to the graph
            $graph->Add($lineplot1);
        }

        if ($this->show_no_reklama) {
            $lineplot2 = new LinePlot($ydata2);
            $lineplot2->SetColor($this->colors[1]);
            $lineplot2->SetWeight(2);
            $lineplot2->SetLegend($this->str('no_reklama'));
            $graph->Add($lineplot2);
        }

        if ($this->show_clients) {
            $lineplot3 = new LinePlot($ydata3);
            $lineplot3->SetColor($this->colors[2]);
            $lineplot3->SetWeight(2);
            $lineplot3->SetLegend($this->str('clients'));
            $graph->Add($lineplot3);
        }

        if ($this->show_uvisit) {
            $lineplot4 = new LinePlot($ydata4);
            $lineplot4->SetColor($this->colors[3]);
            $lineplot4->SetWeight(2);
            $lineplot4->SetLegend($this->str('new_visitors'));
            $graph->Add($lineplot4);
        }

        if ($this->show_hosts) {
            $lineplot5 = new LinePlot($ydata5);
            $lineplot5->SetColor($this->colors[4]);
            $lineplot5->SetWeight(2);
            $lineplot5->SetLegend($this->str('ips'));
            $graph->Add($lineplot5);
        }

        if ($this->show_loads) {
            $lineplot6 = new LinePlot($ydata6);
            $lineplot6->SetColor($this->colors[5]);
            $lineplot6->SetWeight(2);
            $lineplot6->SetLegend($this->str('pages'));
            $graph->Add($lineplot6);
        }
        if ($this->page_id>0) {
            $lineplot7 = new LinePlot($ydata7);
            $lineplot7->SetColor($this->colors[6]);
            $lineplot7->SetWeight(2);
            $lineplot7->SetLegend($this->str('for_page'));
            $graph->Add($lineplot7);
        }

        $y = 8;
        if ($this->events) foreach ($this->events as $key=>$val) {
            $temp = 'show_event_'.$key;
            if ($this->$temp) {
                $temp = 'ydata'.$y;
                $temp2 = 'lineplot'.$y;

                $$temp2 = new LinePlot($$temp);
                if (isset($this->colors[$y-1])) $$temp2->SetColor($this->colors[$y-1]);
                else $$temp2->SetColor($this->colors[$y-1-count($this->colors)]);
                $$temp2->SetWeight(2);
                $$temp2->SetLegend($val['name']);
                $graph->Add($$temp2);
            }
            $y++;
        }

        // Adjust the legend position
        $graph->legend->Pos(0.2, 0.96, 'center', 'bottom');
        $graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);

        // Display the graph
        $graph->Stroke();
    }

    ######################

    function ShowBarGraphImg() {
        $this->Init();

        $_GET['offset'] = 0;
        $_GET['limit'] = -1;
        $data = $this->GetData();

        $y = 8;
        if ($this->events) foreach ($this->events as $key=>$val) {
            $temp = 'ydata'.$y;
            $$temp = array();
            $y++;
        }

        foreach ($data['rows'] as $i => $row) {
            $xdata[] = $row;
            $ydata1[] = isset($data['visit'][$i]) ? (int)$data['visit'][$i] : 0;
            $ydata2[] = isset($data['no_reklama'][$i]) ? (int)$data['no_reklama'][$i] : 0;
            $ydata3[] = isset($data['clients'][$i]) ? (int)$data['clients'][$i] : 0;
            $ydata4[] = isset($data['uvisit'][$i]) ? (int)$data['uvisit'][$i] : 0;
            $ydata5[] = isset($data['hosts'][$i]) ? (int)$data['hosts'][$i] : 0;
            $ydata6[] = isset($data['loads'][$i]) ? (int)$data['loads'][$i] : 0;
            $ydata7[] = isset($data['for_page'][$i]) ? (int)$data['for_page'][$i] : 0;

            $y = 8;
            if ($this->events) foreach ($this->events as $key=>$val) {
                $temp = 'ydata'.$y;
                array_push($$temp, isset($data['event_'.$key][$i])    ? (int)$data['event_'.$key][$i]    : 0);
                $y++;
            }
        }
        $graph_width = $data['count'] * 20;
        if ($graph_width < $this->graph_width)
        $graph_width = $this->graph_width;

        include ('jpgraph/jpgraph.php');
        include ('jpgraph/jpgraph_bar.php');

        // Create the graph. These two calls are always required
        $graph = &new Graph($graph_width, $this->graph_height, 'auto');
        $graph->SetScale('textlin');
        $graph->SetShadow();
        $graph->SetAlphaBlending();

        $graph->img->SetMargin(40, 40, 20, 200);

        if ($this->show_visit) {
            // Create the bar plots
            $b1plot = &new BarPlot($ydata1);
            // Set the colors for the plots
            $b1plot->SetFillColor($this->colors[0]);
            // Set the legends for the plots
            $b1plot->SetLegend($this->str('visitors'));
            $group[] = $b1plot;
        }

        if ($this->show_no_reklama) {
            $b2plot = &new BarPlot($ydata2);
            $b2plot->SetFillColor($this->colors[1]);
            $b2plot->SetLegend($this->str('no_reklama'));
            $group[] = $b2plot;
        }

        if ($this->show_clients) {
            $b3plot = &new BarPlot($ydata3);
            $b3plot->SetFillColor($this->colors[2]);
            $b3plot->SetLegend($this->str('clients'));
            $group[] = $b3plot;
        }

        if ($this->show_uvisit) {
            $b4plot = &new BarPlot($ydata4);
            $b4plot->SetFillColor($this->colors[3]);
            $b4plot->SetLegend($this->str('new_visitors'));
            $group[] = $b4plot;
        }

        if ($this->show_hosts) {
            $b5plot = &new BarPlot($ydata5);
            $b5plot->SetFillColor($this->colors[4]);
            $b5plot->SetLegend($this->str('ips'));
            $group[] = $b5plot;
        }

        if ($this->show_loads) {
            $b6plot = &new BarPlot($ydata6);
            $b6plot->SetFillColor($this->colors[5]);
            $b6plot->SetLegend($this->str('pages'));
            $group[] = $b6plot;
        }
        if($this->page_id > 0 ) {
            $b7plot = &new BarPlot($ydata7);
            $b7plot->SetFillColor($this->colors[6]);
            $b7plot->SetLegend($this->str('for_page'));
            $group[] = $b7plot;
        }

        $y = 8;
        if ($this->events) foreach ($this->events as $key=>$val) {
            $temp = 'show_event_'.$key;
            if ($this->$temp) {
                $temp = 'ydata'.$y;
                $temp2 = 'b'.$y.'plot';

                $$temp2 = &new BarPlot($$temp);
                if (isset($this->colors[$y-1])) $$temp2->SetFillColor($this->colors[$y-1]);
                else $$temp2->SetFillColor($this->colors[$y-1-count($this->colors)]);
                $$temp2->SetLegend($val['name']);
                $group[] = $$temp2;
            }
            $y++;
        }

        // Create the grouped bar plot
        $gbplot = &new GroupBarPlot($group);
        $gbplot->SetWidth(0.9);
        // ...and add it to the graPH
        $graph->Add($gbplot);

        // Setup scale

        $graph->xaxis->SetTickLabels($xdata);
        $graph->xaxis->SetLabelAngle(90);
        $graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
        $graph->xaxis->SetLabelMargin(10);

        // Titles
        $graph->title->SetFont(FF_VERDANA,FS_BOLD);
        $graph->title->Set($this->GetTitle());

        // Adjust the legend position
        $graph->legend->Pos(0.2, 0.96, 'center', 'bottom');
        $graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);

        // Display the graph
        $graph->Stroke();
    }

    ######################

    function GetData() {
        global $limit;
        $offset = (int)get('offset');
        $limit = (int)get('limit', $this->Param('limit', $limit));

        // ѕолучает периоды на которые нужно разбивать
        if ($this->group) {
            // ѕолучает периоды на которые нужно разбивать
            if ($this->disp_by == 'hour') for ($i=0; $i<24; $i++) {
                $rows[$i]	= $i.':00 - '.($i+1).':00';
                $str[$i]	= sprintf($this->str('from_to'), $i.':00', ($i+1).':00');
            }
            elseif ($this->disp_by == 'dayofmonth') for ($i=1; $i<32; $i++) {
                $rows[$i]	=
                $str[$i]	= sprintf($this->str('for_day'), $this->ordSuffix($i));
            }
            elseif ($this->disp_by == 'weekday') for ($i=0; $i<7; $i++) {
                $rows[$i]	= $this->str('sm_dayweek_'.$i);
                $str[$i]	= sprintf($this->str('during'), $this->str('dayweek_'.$i));
            }
            elseif ($this->disp_by == 'month') for ($i=1; $i<13; $i++) {
                $rows[$i]	= $this->str('sm_month_'.$i);
                $str[$i]	= sprintf($this->str('during'), $this->str('month_'.$i));
            }
            $period = $this->disp_by.'(FROM_UNIXTIME(time))';
            $sessperiod = $this->disp_by.'(FROM_UNIXTIME(sess.time))';
        }
        elseif ($this->disp_by == 'hour') {
            $time = $this->from_date;
            $i = 0;
            while ($time <= $this->to_date) {
                $rows[$time] = date('d.m.y H:i', $time);
                $hour = strtotime(strftime('%Y-%m-%d %H:%M:%S', $time) . ' +1 hour');
                $str[$time] = date('d.m.y H:i', $time)."-".date('H:i', $hour);
                $time = $hour;
                $i++;
            }
            $period = 'FLOOR(time/3600)*3600';
            $sessperiod = 'FLOOR(sess.time/3600)*3600';
        }
        elseif ($this->disp_by == 'dayofmonth') {
            $time = $this->from_date;
            $i = 0;
            while ($time <= $this->to_date) {
                $rows[$i] = date('d.m.y', $time);
                $str[$i] = '<a href="#" onclick="SetForm(\''.date('Y-m-d', $time).'\', \''.date('Y-m-d',$time).'\', \'hour\'); return false;"><span class="'.((date("w",$time)==6||date("w",$time)==0) ? 'THoliday' : '').'">'.sprintf($this->str('during'), date('d.m.y', $time)).'</span></a>';
                $time = strtotime(strftime('%Y-%m-%d %H:%M:%S', $time) . ' +1 day');
                $i++;
            }
            $period = 'FLOOR((time - '.$this->from_date.')/86400)';
            $sessperiod = 'FLOOR((sess.time - '.$this->from_date.')/86400)';
        }
        elseif ($this->disp_by == 'weekday') {
            $time = $this->from_date;
            $i = 0;
            while ($time <= $this->to_date) {
                $rows[$i] = date('d.m.y', $time);
                $week = strtotime(strftime('%Y-%m-%d %H:%M:%S', $time) . ' +1 week');
                $str[$i] = '<a href="#" onclick="SetForm(\''.date('Y-m-d', $time).'\', \''.date('Y-m-d', $week).'\', \'dayofmonth\'); return false;">'.date('d.m.y', $time)."-".date('d.m.y', $week).'</a>';
                $time = $week;
                $i++;
            }
            $period = 'FLOOR((time - '.$this->from_date.')/604800)';
            $sessperiod = 'FLOOR((sess.time - '.$this->from_date.')/604800)';
        }
        elseif ($this->disp_by == 'month') {
            $i = date("n", $this->from_date);
            $this->from_date -= 86400 * (date('j',$this->from_date)-1);
            for ($time = $this->from_date; $time <= $this->to_date; $time = mktime(0,0,0,$i++,1,date('Y', $this->from_date))) {
                $rows[date("y-m",$time)] = $this->str('sm_month_'.date('n',$time)).' '.date('Y',$time);
                $str[date("y-m",$time)] = '<a href="#" onclick="SetForm(\''.date('Y-m', $time).'-01\', \''.date('Y-m', $time).'-30\', \'dayofmonth\'); return false;">'.sprintf($this->str('during'), $this->str('month_'.date('n',$time)).' '.date('Y',$time)).'</a>';
            }
            $period = "DATE_FORMAT(FROM_UNIXTIME(time),'%y-%m')";
            $sessperiod = "DATE_FORMAT(FROM_UNIXTIME(sess.time),'%y-%m')";
        }

        $count = count($rows);
        if ($limit>0)
        $rows = $this->SliceArray($rows, $offset, $limit);
        $keys = "'".join("','", array_keys($rows))."'";

        // ѕолучает данные по периодам
        $arr = array(
        'rows' => $rows,
        'str' => $str,
        'visit' => sql_getRows("SELECT ".$period." AS period, COUNT(*) AS count FROM ".$this->sess_table." WHERE robot='0' AND ".$period." IN (".$keys.") GROUP BY period", true),
        'no_reklama' => sql_getRows("SELECT ".$period." AS period, COUNT(*) AS count FROM ".$this->sess_table." AS s, ".STAT_PAGES_TABLE." AS p WHERE s.first_page=p.id AND p.uri NOT LIKE '%?from=%' AND robot='0' AND ".$period." IN (".$keys.") GROUP BY period", true),
        'clients' => sql_getRows("SELECT ".$period." AS period, COUNT(*) AS count FROM ".$this->sess_table." WHERE robot='0' AND client_id!=0 AND ".$period." IN (".$keys.") GROUP BY period", true),
        'loads' => sql_getRows("SELECT ".$period." AS period, SUM(loads) AS count FROM ".$this->sess_table." WHERE robot='0' AND ".$period." IN (".$keys.") GROUP BY period", true),
        'uvisit' => sql_getRows("SELECT ".$period." AS period, COUNT(*) AS count FROM ".$this->sess_table." WHERE robot='0' AND new_visitor='1' AND ".$period." IN (".$keys.") GROUP BY period", true),
        'hosts' => sql_getRows("SELECT ".$period." AS period, COUNT(DISTINCT(ip)) AS count FROM ".$this->sess_table." WHERE robot='0' AND ".$period." IN (".$keys.") GROUP BY period", true),
        'for_page' => sql_getRows("SELECT ".$sessperiod." AS period, COUNT(log.page_id) AS count FROM ".STAT_LOG_TABLE." AS log LEFT JOIN ".$this->sess_table." AS sess ON log.sess_id = sess.sess_id WHERE sess.robot='0' AND log.page_id=".$this->page_id." AND ".$sessperiod." IN (".$keys.") GROUP BY period", true),
        'count' => $count,
        'offset' => $offset,
        'limit' => $limit,
        );

        if ($this->events) {
            $period = str_replace("time","sess.time",$period);
            foreach ($this->events as $key=>$val) {
                $sql = "SELECT ".$period." AS period, COUNT(pages.uri) AS count FROM ".$this->sess_table." AS sess LEFT JOIN ".STAT_LOG_TABLE." AS log ON sess.sess_id=log.sess_id LEFT JOIN ".STAT_PAGES_TABLE." AS pages ON log.page_id=pages.id WHERE robot='0' AND ".$period." IN (".$keys.") AND pages.uri LIKE '".str_replace("*","%",$val['url'])."' GROUP BY period";
                $arr['event_'.$key] = sql_getRows($sql, true);
            }
        }
        if(!$this->page_id) unset($arr['for_page']);
        return $arr;

    }
    ######################
}

$GLOBALS['stat__stat_attendance'] =  & Registry::get('TAttendance');

?>