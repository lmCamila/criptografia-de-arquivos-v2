<?php 

namespace Seguranca;

use Rain\Tpl;

class Page{
private $tpl;
    private $options = [];
    private $defaults = [
        "data"=>[]
    ];
    public function __construct($opts = array(),$tpl_dir = "./views/src/"){
        $this->options = array_merge($this->defaults , $opts);

		$config = array(
			"base_url"      => null,
			"tpl_dir"		=>$_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"chache_dir"	=>$_SERVER["DOCUMENT_ROOT"]."./views-cache/",
			"debug"			=>false
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl;
		
		$this->setData($this->options['data']);
    }
    
	private function setData($data = array())
	{
		foreach($data as $key => $value){
			$this->tpl->assign($key,$value);
		}
    }
    
    public function setTpl($name, $data = array(), $returnHTML = false)
	{
		$this->setData($data);

		return $this->tpl->draw($name,$returnHTML);
    }
    
    public function __destruct(){
        
    }
}

?>