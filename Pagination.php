<?php

declare(strict_types=1);

namespace Bootstrap;

class Pagination
{
    protected array $pagination = [];

    private string  $standard= "
            <li class=\"page-item\">
                <a class=\"page-link\" href=\"?{%getname%}={%getvalue%}{%getothers%}\">{%value%}</a>
            </li>
    ";
    private string  $active = "
            <li class=\"page-item active\" aria-current=\"page\">
                <a class=\"page-link\" href=\"?{%getname%}={%getvalue%}{%getothers%}\">{%value%}<span class=\"sr-only\">(current)</span></a>
            </li>
    ";
    private string  $disabled = "
            <li class=\"page-item disabled\">
                <a class=\"page-link\" href=\"?{%getname%}={%getvalue%}{%getothers%}\" tabindex=\"-1\" aria-disabled=\"true\">{%value%}</a>
            </li>
    ";
    private string $container = "
    <nav aria-label=\"Page navigation\">
        <ul class=\"pagination justify-content-center\">
            {%elements%}
        </ul>
    </nav>
    ";

    public function __construct(array $params = []){
        if (!empty($params)) {
            $this->initPagination($params);
            if (!empty($this->pagination['show'])) $this->echoPagination();
        }
    }

    public function __call(string $method,array $arguments){        
        if (!$this->isSetMethod($method)) return $this;
        $this->setParams($this->keyGenerator($method),(string)$arguments[0]);
        return $this;
    }

    public function initPagination(array $params):self{
        $this->pagination = $params;
        return $this;
    }

    public function generatePagination():string{
        $this->validationKeys();
        $this->validationTemplates();
        $this->shortPages();
        return $this->createPaginationContainer($this->createPaginationElements());
    }

    public function echoPagination():void{
        echo $this->generatePagination();
    }

    private function isSetMethod(string $method):bool{
        if (strpos($method,'set',0) === 0 ) return true;
        echo ("<br>method <b style=\"color:#EE0000;\">${method}</b> not found !<br>");
        return false;
    }

    private function keyGenerator(string $method):string{

        $leters = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        if (strpos($method,'set',0) === 0){
            $method = substr($method,3);
        }

        foreach ($leters as $leter){
            $method = str_replace($leter,'' . strtolower($leter),$method);
        }

        if (strpos($method,'',0) === 0){
            $method = substr($method,1);
        }

        return $method;
    }

    private function validationKeys():void{  
        $this->setParams('page',$this->getParams('page') ?? '1');  
        $this->setParams('pages',$this->getParams('pages') ?? $this->getParams('page'));    
        $this->setParams('length',$this->getParams('length') ?? $this->getParams('pages'));   
        $this->setParams('previous',$this->getParams('previous') ?? '<span aria-hidden="true">&laquo;</span>');  
        $this->setParams('next',$this->getParams('next') ?? '<span aria-hidden="true">&raquo;</span>');  
        $this->setParams('first',$this->getParams('first') ?? '1');  
        $this->setParams('last',$this->getParams('last') ?? $this->getParams('pages'));  
        $this->setParams('more',$this->getParams('more') ?? '...');  
        $this->setParams('getname',$this->getParams('getname') ?? 'page');  
        
        $this->setParams('standard',$this->getParams('standard') ?? $this->standard);  
        $this->setParams('active',$this->getParams('active') ?? $this->active);  
        $this->setParams('disabled',$this->getParams('disabled') ??  $this->disabled);  
        $this->setParams('container',$this->getParams('container') ??  $this->container);   

        if ($this->getParams('page') < 1) $this->setParams('page','1');
        if ($this->getParams('page') > $this->getParams('pages')) $this->setParams('page',$this->getParams('pages'));
        if ($this->getParams('length') < 3) $this->setParams('length','3');
        if ($this->getParams('length')%2 === 0) $this->setParams('length',(string)($this->getParams('length') + 1));
    }

    private function validationTemplates():void{
        $elements = ['standard','active','disabled'];
        $templates = ['{%getname%}','{%getvalue%}','{%value%}'];
        foreach($elements as $element){
            foreach($templates as $template){
                $pos = strpos($this->getParams($element),$template);
                if ($pos === false) echo ("<br>Error $element- template <b style=\"color:#EE0000;\">$template</b> is required !<br>");
            }
        }
        $pos = strpos($this->getParams('container'),'{%elements%}');
        if ($pos === false) echo ("<br>Error container- template <b style=\"color:#EE0000;\">{%elements%}</b> is required !<br>");
        
    }

    private function getParams(string $key):?string {
        if (empty($this->pagination[$key])) return null;
        return((string)$this->pagination[$key]);
    }

    private function setParams(string $key,string $value):void{
        $this->pagination[$key] = $value;
    }

    private function shortPages():void{
        if ($this->getParams('length') < $this->getParams('pages')){
            $this->setParams('begin',(string)($this->getParams('page') - ceil(($this->getParams('length') - 1) / 2) > 1 ? $this->getParams('page') - ceil(($this->getParams('length') - 1)/ 2) : 1));
            $this->setParams('end',(string)($this->getParams('page') + $this->getParams('length') - ($this->getParams('page') - $this->getParams('begin')) - 1));
            if ($this->getParams('end') > $this->getParams('pages')) $this->setParams('begin',(string)($this->getParams('begin') - ($this->getParams('end') - $this->getParams('pages'))));
            if ($this->getParams('begin') < 1) $this->setParams('begin','1');
        }
    }

    private function createPaginationElements():string{
        $elements = $this->createPaginationElement((string)$this->getParams('previous'),(string)($this->getParams('page') - 1),false,$this->getParams('page') == 1);
        if ($this->getParams('length') < $this->getParams('pages') && $this->getParams('begin') > 1) {
            $elements .= $this->createPaginationElement((string)$this->getParams('first'),'1');
            $elements .= $this->createPaginationElement((string)$this->getParams('more'),(string)($this->getParams('begin') - 1));
        }
        for ($i = 1;$i <= $this->getParams('pages');$i++){
            if ($this->getParams('length') < $this->getParams('pages'))
                if ($i < $this->getParams('begin') || $i > $this->getParams('end')) continue;

            $elements .= $this->createPaginationElement((string)$i,(string)$i,$i == $this->getParams('page'));
        }
        if ($this->getParams('length') < $this->getParams('pages') && $this->getParams('pages') > $this->getParams('end') ) {
            $elements .= $this->createPaginationElement((string)$this->getParams('more'),(string)($this->getParams('end') + 1));
            $elements .= $this->createPaginationElement((string)$this->getParams('last'),(string)$this->getParams('pages'));
        }
        $elements .= $this->createPaginationElement((string)$this->getParams('next'),(string)($this->getParams('page') + 1),false,$this->getParams('page') == $this->getParams('pages'));
        return $elements;
    }

    private function createPaginationElement(string $value,string $getvalue ,bool $active = false,bool $disabled = false):string{
        $element = $this->getParams('standard');
        if ($active) $element = $this->getParams('active') ;
        if ($disabled) $element = $this->getParams('disabled') ;
        return \str_replace(['{%getname%}','{%getvalue%}','{%getothers%}','{%value%}'],[$this->getParams('getname'),$getvalue,$this->getParameters(),$value],$element) ;
    }

    private function createPaginationContainer(string $elements):string{
        return \str_replace('{%elements%}',$elements,$this->getParams('container')) ;
    }

    private function getParameters():string{
        $getParams = '';
        foreach($_GET as $key=>$value)
        {
            if ($key == $this->getParams('getname')) continue;
            $getParams .= "&$key=$value";
        }
        return $getParams;
    }
}