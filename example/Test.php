<?php
require_once __DIR__.'/../src/Rest/RestfulServer.php';

class TestService extends \Rest\RestfulServer {

    public function index() {
        echo 'test from index<br>';
        echo basename(__FILE__, '.php'); 
        echo '<br>method<br>';
        var_dump($this->method);
        echo '<br>qrystr<br>';
        var_dump($this->qrystr);
        echo '<br>request path <br>';
        var_dump($this->request);
        echo '<br>reqs path <br>';
        var_dump($this->reqs);
        echo '<br>posts path <br>';
        var_dump($this->posts);
        echo '<br>$this->qrypath <br>';
        var_dump($this->qrypath);
        echo '<br>class method<br>';
    }

    public  function  edit($id)  {
        echo 'edit----',$id;
        $this->rest_error(-1,'test');
    }

    public  function getTest($id,$a=null,$b=null,$c=null,$d=null,$e=null) {
        echo 'getTest---',$id,$a,$b,$c,$d,$e;
    }

    public function postSearch($b=null,$s=null,$k=null,$p=null,$f=null) {
            echo  'brand = ',$b,'<br>series=',$s,'<br>keyword=',$k,'<br>page=',$p,'<br>filter=',$f;
              $numperpage = 30;

                // $f = filter_input(INPUT_POST,'f');
                // if($f){

                // } else {
                //     $f = 0 ;
                // }

                // $p = filter_input(INPUT_POST,'p');
                if($p){
                    $page = $p * $numperpage;
                } else {
                    $page = 0 ;
                }

                // $b = filter_input(INPUT_POST,'b');
                if($b){
                    if($b == -1) {
                        $b = ' ';
                    } else {
                        $b = ' and b.id = ' . $b;
                    }
                }else{
                    $b = ' ';
                }

                // $s = filter_input(INPUT_POST,'s');
                if($s){
                    if($s == -1 ) {
                        $s = ' ';
                    } else {
                        $s =' and s.id = '.$s;
                    }
                }else{
                    $s ='  ';
                }

                // $k = filter_input(INPUT_POST,'k');
                if($k){
                    $k =' and post_title like "%'.$k.'%"';
                }else{
                    $k = ' ';
                }

                switch ($f) {
                    case '1':  //----- chip  > 0
                        $f ='  and chiptuning > 0 ';
                        break;
                    case '2': // ----- control > 0
                        $f = ' and suport_control > 0 ';
                        break;
                    case '3':  //   chip > 0 and control > 0
                        $f = '  and  ( chiptuning > 0 and suport_control > 0 ) ';
                        break;
                    case '4':  //--- chip == 0 and control == 0
                        $f = ' and ( chiptuning = 0  and suport_control = 0  )';
                        break;
                    case '5':  //--- chip == 0 and control == 0
                        $f = ' and  chiptuning = 0 ';
                        break;
                    case '6':  //--- chip == 0 and control == 0
                        $f = ' and suport_control = 0  ';
                        break;
                    default:
                        $f='';
                        break;
                }




                $sql = '
                SELECT
                b.cat_name as brand,
                s.cat_name series,
                cm_posts.post_id id,
                cm_posts.suport_control control,
                cm_posts.chiptuning tuning,
                cm_posts.*
                FROM
                cm_category AS b
                INNER JOIN cm_category AS s ON s.cat_parent = b.id
                INNER JOIN cm_posts ON cm_posts.category_id = s.id
                WHERE 1=1';
                // and b.id = ?
                // and s.id = ?
                // and post_title like "%?%"
                // limit ? ,?
                // ';
                $sql .= $b;
                $sql .= $s;
                $sql .= $k;
                $sql .= $f;
                $sql .= ' limit '.$page.' ,'.$numperpage.'   ';

                $countsql = 'select  ceil(count(*)/'.$numperpage.') as numpage from cm_category AS b
                INNER JOIN cm_category AS s ON s.cat_parent = b.id
                INNER JOIN cm_posts ON cm_posts.category_id = s.id
                WHERE 1=1';
                $countsql .= $b;
                $countsql .= $s;
                $countsql .= $k;
                $countsql .= $f;
                // dump($countsql);
                // dump($sql);

                // $count = Capsule::select($countsql);
                // $cars = Capsule::select($sql);
                // // dump($cars);
                // $o = new stdClass();
                // $o->numpage = $count[0]['numpage'];
                // $o->cars = $cars;
                echo '<br>',$sql;
                // echo json_encode($o);

    }

}

$app = new TestService();
$app->run();

