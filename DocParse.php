<?php




class DocParse
{

    

    protected $property  = [
        'name'=>'interface name',
        'description'=>'interface description',
        'param'=>[],
        'return'=>[]
    ];

    public function parse($str){
        if(preg_match('#^/\*\*(.*)\*/#s', $str, $matches) === false){
            //error($this->tag,'invalid comment');
            return false;
        }
        if(!isset($matches[1])){
            return false;
        }
        $comment = $matches[1];
        //Get all the lines and strip the * from the first character
        if(preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false){
            //error($this->tag,'invalid comment');
            return false;
        }
        if(!isset($lines[1])){
            return false;
        }
        $this->parseLines($lines[1]);
        return true;
    }


    protected function parseLines($lines){
        $description = [];
        $name = false;
        $value = '';
        foreach($lines as $line){
            $var = $this->parseLine($line,$name);
            if($var === false){
                continue;
            }
            if($var['name'] === false){
                array_push($description,$var['value']);
            }else{
                if($var['isComplete']){
                    /**
                     * save prev buffer
                     */
                    if(!empty($value)){
                        $this->setProperty($name,trim($value));
                        $value = '';
                    }
                    /**
                     * save current
                     */
                    $name = $var['name'];
                    $this->setProperty($name,$var['value']);
                }else{
                    $name = $var['name'];
                    if($var['value']){
                        $value .= PHP_EOL.$var['value'];
                    }
                }
            }
        }
        $this->setProperty('description',implode(PHP_EOL,$description));
        if(!empty($value)){
            $this->setProperty($name,$value);
        }
        return true;
    }


    protected function parseLine($line,$prevName = false){
        $line = trim($line);
        if(empty($line)){
            return false;
        }
        $isComplete = true;
        if(strpos($line, '@') === 0) {
            $pos = strpos($line, ' ');
            if($pos == false){
                $name = substr($line, 1);
                $value = false;
                $isComplete = false;
            }else{
                $name = substr($line, 1, strpos($line, ' ') - 1);
                $value = substr($line, strlen($name) + 2);
            }
            return [
                'name'=>$name,
                'value'=>$value,
                'isComplete'=>$isComplete
            ];
        }else{
            $isComplete = false;
        }
        return [
            'name'=>$prevName,
            'value'=>$line,
            'isComplete'=>$isComplete
        ];
    }


    protected function formatValue($value){
        $map = explode(' ',$value,3);
        if(empty($map)){
            return false;
        }
        if(count($map) == 1){
            return [
                'type'=>'null',
                'name'=>ltrim($map[0],'$'),
                'description'=>'null'
            ];
        }else if(count($map) == 2){
            return [
                'type'=>'null',
                'name'=>ltrim($map[0],'$'),
                'description'=>$map[1]
            ];
        }else{
            return [
                'type'=>$map[0],
                'name'=>ltrim($map[1],'$'),
                'description'=>$map[2]
            ];
        }
    }

    protected function setProperty($name,$value){
        $isMulti = false;
        if(in_array($name,['param','return'])){
            $isMulti = true;
        }
        if($isMulti) {
            $value = $this->formatValue($value);
            if($value === false){
                return false;
            }
        }
        if($isMulti){
            if(empty($this->property[$name])){
                $this->property[$name] = [$value];
            }else{
                array_push($this->property[$name],$value);
            }
        }else{
            $this->property[$name] = $value;
        }
        return true;
    }



    protected function buildValue($data){
        $str = sprintf('| %s | %s | %s |'.PHP_EOL,lang('Name'),lang('Type'),lang('Description'));
        $str .= '| --- | --- | --- |'.PHP_EOL;
        foreach ($data as $row){
            $str .= sprintf('| %s | %s | %s |'.PHP_EOL,$row['name'],$row['type'],$row['description']);
        }
        return $str;
    }

    /**
     *
     */
    public function markdown(){
        $doc = [];
        array_push($doc,'## '.$this->property['name']);
        array_push($doc,$this->property['description']);
        array_push($doc,PHP_EOL);
        array_push($doc,'## '.lang('Params'));
        array_push($doc,$this->buildValue($this->property['param']));
        array_push($doc,PHP_EOL);
        array_push($doc,'## '.lang('Return'));
        array_push($doc,$this->buildValue($this->property['return']));
        array_push($doc,PHP_EOL);
        if(!empty($this->property['example'])){
            array_push($doc,'## '.lang('Example'));
            array_push($doc,'```');
            array_push($doc,$this->property['example']);
            array_push($doc,'```');
        }
        array_push($doc,PHP_EOL);
        return implode(PHP_EOL,$doc);
    }
}