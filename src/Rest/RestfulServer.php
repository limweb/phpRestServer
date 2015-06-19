<?php
//============================= Server Start ========================================================
class RestfulServer {

        private    $debug = TRUE;
        private    $start_time;
        protected $host = '/';
        protected $method  = null;
        protected $request  = null;
        protected $qrystr     = null;
        protected $input   = null;
        protected $qrypath = null;
        protected $fills =[];
        protected $posts = [];
        protected $reqs = [];
        protected $format = null;
        protected $loginpath = 'ket_racechip/admin/index.php';
        protected $response = [
               'code' =>0,
               'status' => 404,
               'data' => null,
        ];
                // Define HTTP responses
        protected $http_response_code = [
               200 => 'OK',
               400 => 'Bad Request',
               401 => 'Unauthorized',
               403 => 'Forbidden',
               404 => 'Not Found'
        ];
                // Define whether an HTTPS connection is required
        protected $HTTPS_required = FALSE;
                // Define whether user authentication is required
        protected $authentication_required = false;
                // Define API response codes and their related HTTP response

        protected $api_response_code = array(
            0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
            1 => array('HTTP Response' => 200, 'Message' => 'Success'),
            2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
            3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
            4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
            5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
            6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format')
        );

        private  $methodget = [];
        private  $methodput = [];
        private  $methodpost = [];
        private  $methoddelete = [];
        private  $reservemethod =[
            'getIndex',
            'getcreate',
            'getShow',
            'getEdit',
            'putUpdate',
            'postStore',
            'deleteDestroy',
            'getRoutes',
        ];
        public function __construct() {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
                            // $this->host = 'http://'.$_SERVER['HTTP_HOST'];
            $this->login();
        }

        public  function __destruct() {
               if($this->debug) {
                     $mic_time = microtime();
                     $mic_time = explode(" ",$mic_time);
                     $mic_time = $mic_time[1] + $mic_time[0];
                     $endtime = $mic_time;
                     $total_execution_time = ($endtime - $this->start_time);
                     echo "<br>Total Executaion Time ".$total_execution_time." seconds";
                 }
        }

        private  function starttime() {
             $mic_time = microtime();
             $mic_time = explode(" ",$mic_time);
             $mic_time = $mic_time[1] + $mic_time[0];
             $this->start_time = $mic_time;
        }

        public function run() {
         ( $this->debug ? $this->starttime() : null );
        // var_dump($class_methods);

         $this->preser_function();

        // Set default HTTP response of 'ok'
         $this->method = $_SERVER['REQUEST_METHOD'];
         $this->qrypath = filter_input(INPUT_SERVER, 'PATH_INFO');
        // $this->qrypath = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
         $this->request = filter_input(INPUT_SERVER, 'PATH_INFO');
         $this->request =  rtrim($this->request,"\/");
         $this->request = explode("/", substr(@$this->request, 1));
         $qrystr = filter_input(INPUT_SERVER, 'QUERY_STRING');
         parse_str($qrystr, $this->qrystr);
         $this->input = (object)   json_decode(file_get_contents("php://input"));
         $this->posts = $_POST;
         $this->reqs = $_REQUEST;
         $this->format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_SPECIAL_CHARS);
                            // ($this->format ? :$this->format='json');
         $func  = filter_input(INPUT_GET, 'method', FILTER_SANITIZE_SPECIAL_CHARS);
                            // ($func ? :$func = 'hello');
                              // echo 'this class = ',get_class($this),"\n";
         if(  get_class($this) == 'RestfulServer' ) { 
               echo  'Restful Server v.0.0.1',"\n<br>";
               echo  '--------------------------------',"\n<br>";
               echo  '  class   YourService extends RestfulServer  {',"\n<br>";
               echo  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;---- you function ----',"\n<br>";
               echo  '  } ',"\n<br>";
               echo  ' $app = new YourService();',"\n<br>";
               echo  ' $app->run()',"\n<br>";
               echo  '--------------------------------',"\n<br>";
        } else  {

            // print_r($this->methodget);
            // print_r($this->request);
            // var_dump($this->methodget);
            // var_dump($this->methodput );
            // var_dump($this->methodpost);
            // var_dump($this->methoddelete);
            // exit();
            if( $this->request ) {
                $this->request[] = '';
            }

            switch ($this->method) {
                case 'GET':
                foreach ($this->methodget as $get) {
                    $get =(object) $get;
                    if(strtolower($get->path) == strtolower($this->request[0])){
                        array_shift($this->request);
                        call_user_func_array([$this,$get->method], $this->request );
                        return;
                    }
                }
                $this->rest_error(-1,'Error: '.$this->request[0].' method not found.','');
                break;
                case 'PUT':
                foreach ($this->methodput as $put) {
                    $put =(object) $put;
                    if(strtolower($put->path) == strtolower($this->request[0])){
                        array_shift($this->request);
                        call_user_func_array([$this,$put->method], $this->request );
                        return;
                    }
                }
                if($this->request[0]){
                    $this->update($this->request[0]);
                } else {
                    $this->rest_error(-1,'Error: '.$this->request[0].' method not found.','');
                }
                break;
                case 'POST':
                foreach ($this->methodpost as $post) {
                    $post =(object) $post;
                    if(strtolower($post->path) == strtolower($this->request[0])){
                        array_shift($this->request);
                        call_user_func_array([$this,$post->method], $this->request );
                        return;
                    }
                }
                $this->rest_error(-1,'Error: '.$this->request[0].' method not found.','');
                break;
                case 'DELETE':
                foreach ($this->methoddelete as $delete) {
                    $delete =(object) $delete;
                    if(strtolower($delete->path) == strtolower($this->request[0])){
                        array_shift($this->request);
                            call_user_func_array([$this,$delete->method], $this->request );
                            return;
                        }
                    }
                if($this->request[0]){
                    $this->destroy($this->request[0]);
                } else {
                    $this->rest_error(-1,'Error: '.$this->request[0].' method not found.','');
                }
                break;
                case 'HEAD':
                    $this->rest_head();
                break;
                case 'OPTIONS':
                    $this->rest_options();
                break;
                default:
                    $err = 'error';
                    $this->rest_error(-1,$err);
                break;
                }
            }
        }


        public function rest_error($errno,$msg,$format='json'){ 
                $this->response['code'] = 0;
                $this->response['errno'] = $errno;
                $this->response['status'] = $this->api_response_code[ $this->response['code'] ]['HTTP Response'];
                $this->response['data'] = $msg;
                $this->deliver_response($format, $this->response);
            return;
        }

        protected function routes() {
            $html = '<table width="600" border="1">
                    <tr>
                     <th align="center" width ="20">No</th>
                     <th align="center"width ="80">Type</th>
                     <th align="left" width="250">&nbsp;&nbsp;Path</th>
                     <th align="left" width="250">&nbsp;&nbsp;Method</th>
                   </tr><tbody>';
    
           $i = 1;
            foreach ($this->methodget as $method) {
                        $method = (object) $method;
                    $html .=  '<tr><td align="center">'.$i.'</td><td align="center">GET</td><td align="left">&nbsp;&nbsp;/'.$method->path.'</td><td align="left">&nbsp;&nbsp;'.$method->method.'</td></tr>';
                    $i++;
            }
            foreach ($this->methodput as $method) {
                        $method = (object) $method;
                    $html .=  '<tr><td align="center">'.$i.'</td><td align="center">PUT</td><td align="left">&nbsp;&nbsp;/'.$method->path.'</td><td align="left">&nbsp;&nbsp;'.$method->method.'</td></tr>';
                    $i++;
            }
            foreach ($this->methodpost as $method) {
                        $method = (object) $method;
                    $html .=  '<tr><td align="center">'.$i.'</td><td align="center">POST</td><td align="left">&nbsp;&nbsp;/'.$method->path.'</td><td align="left">&nbsp;&nbsp;'.$method->method.'</td></tr>';
                    $i++;
            }
            foreach ($this->methoddelete as $method) {
                        $method = (object) $method;
                    $html .=  '<tr><td align="center">'.$i.'</td><td align="center">DELETE</td><td align="left">&nbsp;&nbsp;/'.$method->path.'</td><td align="left">&nbsp;&nbsp;'.$method->method.'</td></tr>';
                    $i++;
            }
            $html .= '</tbody></table>';
            // echo '<br>';
            // echo '-----------------------'.get_class($this).'----------------------------------<br>';
            // echo  'get =============> ',get_class($this),'.php<br>';
            // echo  'get routes =========> ',get_class($this),'.php/routes  this<br>';
            // echo  'get by id= =========> ',get_class($this),'.php/show/$id<br>';
            // echo  'put =============> ',get_class($this),'.php/$id  & object json<br>';
            // echo  'post ============> ',get_class($this),'.php & object json<br>';
            // echo  'post search =======> ',get_class($this),'.php/search & object json<br>';
            // echo  'delete ===========>',get_class($this),'.php/$id<br>';
            // echo '-----------------------'.get_class($this).'----------------------------------<br>';
            echo $html;
        }

        protected  function deliver_response($format=null, $api_response){
                    // Set HTTP Response
            header('HTTP/1.1 '.$api_response['status'].' '.$this->http_response_code[ $api_response['status'] ]);
                    // Process different content types
            if( strcasecmp($format,'json') == 0 ){
                    // Set HTTP Response Content Type
                header('Content-Type: application/json; charset=utf-8');
                    // Format data into a JSON response
                $json_response = json_encode($api_response);
                    // Deliver formatted data
                echo $json_response;
            }elseif( strcasecmp($format,'xml') == 0 ){
                    // Set HTTP Response Content Type
                header('Content-Type: application/xml; charset=utf-8');
                    // Format data into an XML response (This is only good at handling string data, not arrays)
                $xml_response = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                '<response>'."\n".
                "\t".'<code>'.$api_response['code'].'</code>'."\n".
                "\t".'<data>'.$api_response['data'].'</data>'."\n".
                '</response>';
                    // Deliver formatted data
                echo $xml_response;
            }else{
                    // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
                header('Content-Type: text/html; charset=utf-8');
                    // Deliver formatted data
                echo $api_response['data'];
            }
                    // End script process
            exit;
        }

        protected function login() {
                            // var_dump($_SESSION);
            if( $this->authentication_required ){
                                // if( empty($_POST['username']) || empty($_POST['password']) ){
                if( empty($_SESSION['user'])){
                    $this->response['code'] = 3;
                    $this->response['status'] = $this->api_response_code[ $this->response['code'] ]['HTTP Response'];
                    $this->response['data'] = $this->api_response_code[ $this->response['code'] ]['Message'];
                                    // Return Response to browser
                                    // $this->deliver_response($this->format, $this->response);
                    $path =$this->host.$this->loginpath;
                                    // echo 'path=',$path;
                    header('location: '.$path);
                    exit(0);
                }
                        // Return an error response if user fails authentication. This is a very simplistic example
                        // that should be modified for security in a production environment
                            // elseif( $_POST['username'] != 'foo' && $_POST['password'] != 'bar' ){
                            //     $this->response['code'] = 4;
                            //     $this->response['status'] = $this->api_response_code[ $this->response['code'] ]['HTTP Response'];
                            //     $this->response['data'] = $this->api_response_code[ $this->response['code'] ]['Message'];
                            //     // Return Response to browser
                            //     $this->deliver_response($this->format, $this->response);
                            // }
                else {
                            // $this->response['code'] = 1;
                            // $this->response['status'] = $this->api_response_code[ $this->response['code'] ]['HTTP Response'];
                            // $this->response['data'] = $this->api_response_code[ $this->response['code'] ]['Message'];
                            // // Return Response to browser
                            // $this->deliver_response($this->format, $this->response);
                    return TRUE;
                }

            }
        }

        protected function  prepared(&$rs,$input){
                    if((isset($this->fills) & $this->fills ) & ( isset($input) & $input != new stdClass())){
                        foreach ($this->fills as $field) {
                            if (array_key_exists($field, $input)) {
                             $rs->$field = $input->$field;
                            }
                        }
                    }
        }

        private  function  preser_function() {
                        $class_methods = get_class_methods(get_class($this));
                        foreach ($class_methods as $method) {
                            if ( in_array($method,$this->reservemethod) ){
                                    $this->rest_error(-9,$method . ' is reserve function.');
                                    exit();
                            } else {
                                    if( preg_split('@(?=[A-Z])@', $method)[0] == 'get' ){
                                                // echo 'get---->',strtolower($method),"\n";
                                        $this->methodget[] = [ 'method'=>$method,  'path' => explode('get',$method)[1]  ];
                                    }elseif( preg_split('@(?=[A-Z])@', $method)[0] == 'put' ){
                                                // echo 'put---->',$method,"\n";
                                        $this->methodput[] = ['method'=>$method , 'path'=> explode('put',$method)[1]  ];
                                    }elseif( preg_split('@(?=[A-Z])@', $method)[0] == 'post' ){
                                                // echo 'post--->',$method,"\n";
                                        $this->methodpost[] = ['method'=>$method,'path'=>explode('post',$method)[1]  ];
                                    }elseif( preg_split('@(?=[A-Z])@', $method)[0] == 'delete' ){
                                                // echo 'delete---->',$method,"\n";
                                        $this->methoddelete[] = ['method'=>$method,'path'=>explode('delete',$method)[1]  ];
                                    }
                            }
                        }

                        $this->methodget[] = ['method'=>'index','path'=>''];    //index()                /get---- getIndex
                        $this->methodget[] = ['method'=>'create','path'=>'create']; //   create()      /create/get----   getcreate
                        $this->methodget[] = ['method'=>'show','path'=>'show'];   // show($id)     /show/get----  getShow
                        $this->methodget[] = ['method'=>'edit','path'=>'edit'];    //edit($id)           /edit/get---- getEdit
                        $this->methodget[] = ['method'=>'routes','path'=>'routes'];    //edit($id)           /edit/get---- getEdit
                        $this->methodput[] = ['method'=>'update','path'=>'']; //    update($id)        /put--  putUpdate
                        $this->methodpost[] = ['method'=>'store','path'=>'']; //    store()                 /post--  postStore
                        $this->methoddelete[]=  ['method'=>'destroy','path'=>'']; //    destroy($id)      /delete--  deleteDestroy
        }

        public function index()  {}
        public function create(){}
        public function store(){}
        public function show($id){}
        public function edit($id){ }
        public function update($id){}
        public function destroy($id){}
        public function searchs(){}
        public function rest_options() {}
        public function rest_head() {}

}
//============================= Server run ======================================================= 
   // $app = new  RestfulServer();
   // $app->run();
//===================================================================================== end clasee