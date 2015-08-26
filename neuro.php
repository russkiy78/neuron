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
    // array('cell' => {id, -1}, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
    public $inputs = array();
    // array('cell' => {id, -1}, 'state' => 0)
    public $outputs = array();

    public function DeleteInput($id)
    {
        for ($i = 0; $i < count($this->inputs); $i++) {
            if ($this->inputs[$i]['cell'] == $id) {
                $this->inputs[$i]['cell'] = -1;
            }
        }
        return true;
    }

    public function DeleteOutput($id)
    {
        for ($i = 0; $i < count($this->outputs); $i++) {
            if ($this->outputs[$i]['cell'] == $id) {
                $this->outputs[$i]['cell'] = -1;
            }
        }
        return true;
    }

    public function AddInput($id)
    {
        for ($i = 0; $i < count($this->inputs); $i++) {
            if ($this->inputs[$i]['cell'] == -1) {
                $this->inputs[$i]['cell'] = $id;
                return true;
            }
        }
        return false;
    }

    public function AddOutput($id)
    {
        for ($i = 0; $i < count($this->outputs); $i++) {
            if ($this->outputs[$i]['cell'] == -1) {
                $this->outputs[$i]['cell'] = $id;
                return true;
            }
        }
        return false;
    }

    public function GetFreeInputs()
    {
        $count = 0;
        for ($i = 0; $i < count($this->inputs); $i++) {
            if ($this->inputs[$i]['cell'] == -1) $count++;
        }
        return ($count) ? $count : false;
    }

    public function GetFreeOutputs()
    {
        $count = 0;
        for ($i = 0; $i < count($this->outputs); $i++) {
            if ($this->outputs[$i]['cell'] == -1) $count++;
        }
        return ($count) ? $count : false;
    }

    public function FindInputByCellId($id)
    {
        for ($i = 0; $i < count($this->inputs); $i++) {
            if ($this->inputs[$i]['cell'] == $id) {
                return $i;
            }
        }
        return -1;
    }

    public function FindOutputByCellId($id)
    {
        for ($i = 0; $i < count($this->outputs); $i++) {
            if ($this->outputs[$i]['cell'] == $id) {
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
    public $thisinput;

    public function __construct()
    {

        $this->type = 'neuron';
        $this->phi = 0;
        $this->maxinputs = 2;
        $this->maxoutputs = 1;
        $this->energy = 1;
        $this->output_weight = 1;
        $this->lifetime = 10;
        $this->state = 0;
        $this->dead = false;
    }

    public function InitInputs()
    {
        if ($this->maxinputs >= 2) {
            $this->maxinputs = rand(2, $this->maxinputs);
            for ($i = 0; $i < $this->maxinputs; $i++) {
                array_push($this->inputs,
                    array('cell' => -1, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
                );
            }
            return true;
        } else {
            return false;
        }
    }

    public function InitOutputs()
    {
        for ($i = 0; $i < $this->maxoutputs; $i++) {
            array_push($this->outputs,
                array('cell' => -1, 'state' => 0)
            );
        }
        return true;
    }

}

class sensor extends cell
{

    public function __construct()
    {
        $this->type = 'sensor';
        $this->maxinputs = 0;
        $this->maxoutputs = 3;
        $this->output_weight = 1;
        $this->state = 0;
        $this->dead = false;
    }

    public function InitInputs()
    {
        return true;
    }

    public function InitOutputs()
    {
        for ($i = 0; $i < $this->maxoutputs; $i++) {
            array_push($this->outputs,
                array('cell' => -1, 'state' => 0)
            );
        }
        return true;
    }

}

class summator extends cell
{

    public function __construct()
    {
        $this->type = 'summator';
        $this->maxinputs = 3;
        $this->maxoutputs = 0;
        $this->output_weight = 1;
        $this->state = 0;
        $this->dead = false;
    }

    public function InitInputs()
    {
        if ($this->maxinputs >= 2) {
            for ($i = 0; $i < $this->maxinputs; $i++) {
                array_push($this->inputs,
                    array('cell' => -1, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
                );
            }
            return true;
        } else {
            return false;
        }
    }

    public function InitOutputs()
    {
        return true;
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

        $new->InitInputs();
        $new->InitOutputs();

        if (is_array($params)) {
            foreach ($params as $tname => $tparam) {
                $this->$tname = $tparam;
            }
        }
        array_push($this->net, $new);
        return $this->GetById($new->id);

    }

    public function ConnectCell($id)
    {
        if (is_object($this->net[$id])) {
            $obj = &$this->net[$id];
            // neuron
            if ($obj->type == 'neuron') {

                // output
                $freeout = $obj->GetFreeOutputs();
                if ($freeout)
                    for ($i = 0; $i < $freeout; $i++) {
                        $randsummator = $this->GetFreeInputRandCell('summator', array($obj->id));
                        $randneuro = $this->GetFreeInputRandCell('neuron', array($obj->id));

                        // try summator first
                        if ($randsummator !== false) {
                            $this->net[$randsummator]->AddInput($obj->id);
                            $obj->AddOutput($this->net[$randsummator]->id);
                        } else if ($randneuro !== false) {
                            // other neuro
                            $this->net[$randneuro]->AddInput($obj->id);
                            $obj->AddOutput($this->net[$randneuro]->id);
                        }
                    }

                // input
                $freein = $obj->GetFreeInputs();
                if ($freein)
                    for ($i = 0; $i < $freein; $i++) {
                        $randsensor = $this->GetFreeOutputRandCell('sensor', array($obj->id));
                        $randneuro = $this->GetFreeOutputRandCell('neuron', array($obj->id));

                        // try summator first
                        if ($randsensor !== false) {
                            $this->net[$randsensor]->AddOutput($obj->id);
                            $obj->AddInput($this->net[$randsensor]->id);
                        } else if ($randneuro !== false) {
                            // other neuro
                            $this->net[$randneuro]->AddOutput($obj->id);
                            $obj->AddInput($this->net[$randneuro]->id);
                        }
                    }

            }


        }
    }

    public function IsSensor($obj)
    {
        return (is_object($obj) && $obj->type == 'sensor') ? true : false;
    }

    public function IsSummator($obj)
    {
        return (is_object($obj) && $obj->type == 'summator') ? true : false;
    }

    public function GetFreeInputRandCell($type = 'neuron', $exclude = array())
    {
        $rand = array();
        foreach ($this->net as $cid => $cell) {
            if ($cell->type == $type && $cell->GetFreeInputs() && !in_array($cell->id, $exclude)) {
                array_push($rand, $cid);
            }
        }
        return (count($rand)) ? $rand[array_rand($rand)] : false;
    }

    public function GetByType($type = 'neuron')
    {
	$result = array();
	foreach ($this->net as $cid => $cell) {
	    if ($cell->type == $type  ) {
		array_push($result, $cid);
	    }
	}
	return (count($result)) ? $result : false;
    }

    public function GetFreeOutputRandCell($type = 'neuron', $exclude = array())
    {
        $rand = array();
        foreach ($this->net as $cid => $cell) {
            if ($cell->type == $type && $cell->GetFreeOutputs() && !in_array($cell->id, $exclude)) {
                array_push($rand, $cid);
            }
        }
        return (count($rand)) ? $rand[array_rand($rand)] : false;
    }

    public function GetRandCell($type = 'neuron')
    {
        $rand = array();
        foreach ($this->net as $cid => $cell) {
            if ($cell->type == $type) {
                array_push($rand, $cid);
            }
        }
        return (count($rand)) ? $rand[array_rand($rand)] : false;
    }
}

class wave {
    public $paths = array();
}