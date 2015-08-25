<?php

/**
 * Created by PhpStorm.
 * User: russkiy
 * Date: 23.08.15
 * Time: 15:28
 */
class cell
{

    public $id;
    public $type;
    public $dead;
    public $state;
    public $output_weight;
    public $maxinputs;
    public $maxoutputs;

    // i/o
    // array('from' => id, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
    public $inputs = array();
    // array('to' => 3, 'state' => 0)
    public $outputs = array();

    public function GetFreeInputs() {
        $count =count($this->inputs);
        return ($count<$this->maxinputs) ? $this->maxinputs-$count : false;
    }

    public function GetFreeOutputs() {
        $count =count($this->outputs);
        return ($count<$this->$maxoutputs) ? $this->$maxoutputs-$count : false;
    }

    public function FindInputByFromId($id)
    {
        for ($i = 0; $i < count($this->inputs); $i++) {
            if ($this->inputs[$i]['from'] == $id) {
                return $i;
            }
        }
        return -1;
    }

    public function FindOutputByToId($id)
    {
        for ($i = 0; $i < count($this->outputs); $i++) {
            if ($this->outputs[$i]['to'] == $id) {
                return $i;
            }
        }
        return -1;
    }

    public function ClearAllStates()
    {
        $this->state = 0;
        foreach ($this->inputs as $tid => $t) $this->inputs[$tid]['state'] = 0;
        foreach ($this->outputs as $tid => $t) $this->outputs[$tid]['state'] = 0;
    }

}

class neuron extends cell
{

    public $phi;
    public $energy;
    public $lifetime;

    public function __construct()
    {

        $this->type = 'neuron';
        $this->phi = 0;
        $this->maxinputs = 2;
        $this->maxoutputs = 1;
        $this->energy = 0;
        $this->output_weight = 1;
        $this->lifetime = 10;
        $this->state = 0;
        $this->dead = false;
    }

}

class sensor extends cell
{

    public function __construct()
    {
        $this->type = 'sensor';
        $this->maxinputs = 0;
        $this->maxoutputs = 10;
        $this->output_weight = 1;
        $this->state = 0;
        $this->dead = false;
    }

}

class summator extends cell
{

    public function __construct()
    {
        $this->type = 'summator';
        $this->maxinputs = 10;
        $this->maxoutputs = 0;
        $this->output_weight = 1;
        $this->state = 0;
        $this->dead = false;
    }
}

class net
{
    public $net = array();
    public $paths = array();

    public function GetMaxId()
    {
        $max = -1;
        foreach ($this->net as $cell) {
            if (is_object($cell) && $max < $cell->id) {
                $max = $cell->id;
            }
        }
        return $max;
    }

    public function DeleteCell($id)
    {
        if ($this->GetById($id) !== false) {
            $max = $this->GetMaxId();
            if ($id != $max) {
                foreach ($this->net as $cid => $cell) {
                    if ($cell->id > $max) {
                        $this->net[$cid]->id--;
                    }
                }
            }
            unset ($this->net[$id]);
            return true;
        }
        return false;
    }

    public function GetById($id)
    {
        if (is_integer($id) && $id > -1)
            foreach ($this->net as $cid => $cell) {
                if ($cell->id == $id) {
                    return $cid;
                }
            }
        return false;
    }

    public function AddCell($type = 'neuron', $params = array())
    {
        try {
            $new = new $type ();
        } catch (Exception $e) {
            print $e->getMessage();
            return false;
        }
        $new->id = $this->GetMaxId() + 1;
        if (is_array($params)) {
            foreach ($params as $tname => $tparam) {
                $this->$tname = $tparam;
            }
        }
        array_push($this->net, $new);
        return $this->GetById($new->id);

    }

    public function ConnectCell($id) {

    }

    public function IsSensor($obj)
    {
        return (is_object($obj) && $obj->type == 'sensor') ? true : false;
    }

    public function IsSummator($obj)
    {
        return (is_object($obj) && $obj->type == 'summator') ? true : false;
    }
}