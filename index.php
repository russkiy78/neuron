<?php
require_once 'neuro.php';
/**
 * Created by PhpStorm.
 * User: russkiy
 * Date: 23.08.15
 * Time: 15:29
 */


define('SENSORS', 2);
define('SUMMATORS', 1);
define('LESSONS', 1000);
define('MAXSTEP', 1000);

// testing data
$data = array(
    array('send' => array(0, 0), 'result' => 0),
//  array('send' => array(0, 1), 'result' => 1),
    // array('send' => array(1, 0), 'result' => 1),
    // array('send' => array(1, 1), 'result' => 0)
);

// create

$net = new net();
$path = array();

// 2 sensor 1 summator


$net->AddCell('sensor');
$net->AddCell('sensor');
$net->AddCell('summator');

// first cell
$net->ConnectCell($net->AddCell('neuron'));

//  here must be  lessons counter

foreach ($data as $tdata) {

    //at first init sensors and create paths

    $sensors = $net->GetByType('sensor');
    foreach ($sensors as $tsensor) {

	// create  paths
	foreach ($net->net[$tsensor]->outputs as $ixout => $tout) {
	    if ($tout['cell'] > -1) {
		array_push($path,
		    array('success' => 0, 'history' => array($tout['cell']), 'stop' => 0, 'point' => $tout['cell'])
		);
		$ixinp = $net->net[$tout['cell']]->FindInputByCellId($net->net[$tsensor]->id);
		$net->net[$tout['cell']]->state += $net->net[$tout['cell']]->inputs[$ixinp]['type'] * $net->net[$tsensor]->output_weight;
	    }
	}
	// start path
	/*
	$stepcount = 0;
	 do {


	     $stepcount++;
	 } while($stepcount<MAXSTEP);
*/

    }

}


print_r($net);
print_r($path);

exit;


function CreateNeuro(&$neuronet, $newenergy)
{
    $neuro = new neuron();
    $neuro->energy = $newenergy;
    $neuro->id = count($neuronet);
    $randneuronet = $neuronet;
    shuffle($randneuronet);


    // пытаемся выход к  сумматору  если нет к другим нейронам
    $sucout = -1;
    if ($neuronet[2]->maxinputs > 0) {
	$sucout = 2;
	$neuro->output_to = 2;
    } else {
	for ($i = 0; $i < count($randneuronet); $i++) {
	    if ($randneuronet[$i]->dead) continue;
	    if ($randneuronet[$i]->type != 'summator'
		&& $randneuronet[$i]->type != 'sensor'
		&& $randneuronet[$i]->maxinputs > 0
	    ) {
		$sucout = $randneuronet[$i]->id;
		$neuro->output_to = $randneuronet[$i]->id;
	    }
	}
    }

    if ($sucout == -1) {
	// место кончилось!
	echo "end of  outputs!\n";
	return false;
    }
    // пытаемся подконнектиться к сенсорам если нет к другим нейронам

    $successconnect = 0;

    $connectcount = rand(1, $neuro->maxinputs);
    for ($j = 1; $j <= $connectcount; $j++) {
	if ($neuronet[0]->maxoutputs > 0 && $neuro->FindFrom(0) == -1) {
	    $successconnect = 1;
	    array_push($neuro->inputs, array('from' => 0, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0));
	    $neuronet[0]->maxoutputs--;
	    $neuro->maxinputs--;
	    array_push($neuronet[0]->output, array('to' => $neuro->id, 'state' => 0));
	} else if ($neuronet[1]->maxoutputs > 0 && $neuro->FindFrom(1) == -1) {
	    $successconnect = 1;
	    array_push($neuro->inputs, array('from' => 1, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0));
	    $neuronet[1]->maxoutputs--;
	    $neuro->maxinputs--;
	    array_push($neuronet[1]->output, array('to' => $neuro->id, 'state' => 0));
	} else {
	    for ($i = 0; $i < count($randneuronet); $i++) {
		if ($randneuronet[$i]->dead) continue;
		if ($randneuronet[$i]->type != 'summator'
		    && $randneuronet[$i]->type != 'sensor'
		    && $randneuronet[$i]->output_to == -1
		) {
		    $successconnect = 1;
		    $neuro->maxinputs--;
		    array_push($neuro->inputs,
			array('from' => $randneuronet[$i]->id, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0));
		    $neuronet[$randneuronet[$i]->id]->output_to = $neuro->id;
		}
	    }
	}

    }
    if (!$successconnect) {
	// место кончилось!
	echo "end of  inputs!\n";
	return false;
    }

    if ($sucout > -1) {
	$neuronet[$sucout]->maxinputs--;
	array_push($neuronet[$sucout]->inputs,
	    array('from' => $neuro->id, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
	);

	array_push($neuronet, $neuro);
	return true;
    }
    return false;

}


$sensors = array(new sensor(), new sensor());
$summator = array(new summator());


$neuronet = array_merge($sensors, $summator, array(new neuron()));

// init sensors
$neuronet[0]->output = array(array('to' => 3, 'state' => 0));
$neuronet[0]->maxoutputs--;
$neuronet[1]->output = array(array('to' => 3, 'state' => 0));
$neuronet[1]->maxoutputs--;


// init summator
$neuronet[2]->maxinputs--;
$neuronet[2]->inputs = array(
    array('from' => 3, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
);

// init first neuro
$neuronet[3]->id = 3;
$neuronet[3]->maxinputs -= 2;
$neuronet[3]->inputs = array(
    array('from' => 0, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0),
    array('from' => 1, 'type' => (rand(0, 1) ? -1 : 1), 'state' => 0)
);

$neuronet[3]->output_to = 2;
$neuronet[3]->energy = 3000;


/*end of init*/

$data = array(
    array('send' => array(0, 0), 'result' => 0),
    array('send' => array(0, 1), 'result' => 1),
    array('send' => array(1, 0), 'result' => 1),
    array('send' => array(1, 1), 'result' => 0)
);


// trys!

for ($trys = 0; $trys < 1000; $trys++) {

    // ex
    $suc_exe = array();
    $summas = array();

    for ($exebition = 0; $exebition < count($data); $exebition++) {


	//new paths
	$path = array();

	// init sensors, init summator
	for ($sens = 0; $sens < count($data[$exebition]['send']); $sens++) {
	    for ($i = 0; $i < count($neuronet[$sens]->output); $i++) {

		// find neuron input
		$nextneuro = $neuronet[$sens]->output[$i]['to'];
		$inputid = $neuronet[$nextneuro]->FindFrom($sens);
		if ($inputid > -1) {
		    // finded
		    array_push($path, array('success' => 0, 'history' => array($sens, $nextneuro), 'stop' => 0, 'path' => $nextneuro));
		    $neuronet[$nextneuro]->state += $neuronet[$nextneuro]->inputs[$inputid]['type'];
		} else {
		    echo "error no find!! $nextneuro \n";
		}

	    }
	}
// end init sensors, init summator


	//start
	// do while no success
	$counter = 0;
	do {
	    //  echo "$counter\n";
	    $tpath = array();
	    $livepath = 0;

	    for ($i = 0; $i < count($path); $i++) {

		// live paths only
		if (!$path[$i]['stop']) {
		    $livepath = 1;
		    if ($neuronet[$path[$i]['path']]->state >= $neuronet[$path[$i]['path']]->phi
			&& $neuronet[$path[$i]['path']]->output_to > -1
		    ) {

			$nextneuro = $neuronet[$path[$i]['path']]->output_to;
			$inputid = $neuronet[$nextneuro]->FindFrom($path[$i]['path']);
			if ($inputid > -1) {
			    // finded
			    $neuronet[$nextneuro]->state += $neuronet[$nextneuro]->inputs[$inputid]['type'];
			    if ($neuronet[$nextneuro]->type == 'summator') {
				$path[$i]['stop'] = 1;
				$path[$i]['success'] = 1;
			    }
			    // add history and next iteration
			    array_push($path[$i]['history'], $nextneuro);
			    $path[$i]['path'] = $nextneuro;
			} else {
			    echo " $nextneuro error no find!! \n";
			    exit;
			}
		    } else {
			$path[$i]['stop'] = 1;
		    }
		}

	    }
	    $counter++;
	} while ($livepath && $counter < 1000);

	// all path success (or not)

	// check result
	//todo make correct later!
	$summartoroutput = ($neuronet[2]->state >= $neuronet[2]->phi ? 1 : 0);
	$summas[$exebition] = $summartoroutput;
	if ($summartoroutput == $data[$exebition]['result']) {
	    echo "arbaiten!!\n";
	    $suc_exe[$exebition] = 1;
	    $minpath = 0;
	    $minpathid = -1;

	    // награждаем!

	    for ($i = 0; $i < count($path); $i++) {
		if ($path[$i]['success']) {
		    // ищем самый короткий
		    if ($minpath == 0 || count($path[$i]['history']) < $minpath) {
			$minpath = count($path[$i]['history']);
			$minpathid = $i;
		    }
		    //
		    //    $tpath=array_unique($path[$i]['history']);
		    //     foreach ($tpath as $th) {
		    //       if ($neuronet[$th]->type != 'summator'
		    //            && $neuronet[$th]->type != 'sensor') {
		    //  $neuronet[$th]->energy++;
		    //     }
		    // }
		}
	    }

	    // продлеваем жизнь самым успешным
	    if ($minpath > 0) {
		for ($i = 0; $i < count($path); $i++) {
		    if ($path[$i]['success'] && count($path[$i]['history']) == $minpath) {
			$tpath = array_unique($path[$i]['history']);
			foreach ($tpath as $th) {
			    if ($neuronet[$th]->type != 'summator'
				&& $neuronet[$th]->type != 'sensor'
			    ) {
				//  $neuronet[$th]->output_weight+=2;
				$neuronet[$th]->energy += 2;
			    }
			}
		    }
		}

	    }

	} else {
	    echo "niht arbaiten!!\n";
	    $suc_exe[$exebition] = 0;
	}

	// старим
	//  echo "old\n";
	for ($i = 0; $i < count($neuronet); $i++) {
	    if ($neuronet[$i]->dead) continue;
	    if ($neuronet[$i]->type != 'summator' && $neuronet[$i]->type != 'sensor') {
		$neuronet[$i]->lifetime--;
	    }
	}
	// at first убиваем
	//  echo "kill\n";
	for ($i = 0; $i < count($neuronet); $i++) {
	    if ($neuronet[$i]->dead) continue;
	    if ($neuronet[$i]->type != 'summator'
		&& $neuronet[$i]->type != 'sensor'
		&& $neuronet[$i]->lifetime < 1
		&& $neuronet[$i]->energy < 2
	    ) {
		//убиваем
		if (count($neuronet[$i]->inputs)) {
		    foreach ($neuronet[$i]->inputs as $tinp) {
			// отключаем всех кто на нее смотрел
			if (!$neuronet[$tinp['from']]) {
			    echo "id={$i}  from={$tinp['from']}  error !";
			    print_r($neuronet);
			    exit;
			}
			if ($neuronet[$tinp['from']]->type != 'sensor') {
			    $neuronet[$tinp['from']]->output_to = -1;
			} else {
			    // если это был сенсор!
			    $tto = $neuronet[$tinp['from']]->FindTo($i);
			    if ($tto > -1) {
				$neuronet[$tinp['from']]->maxoutputs++;
				array_splice($neuronet[$tinp['from']]->output, $tto, 1);
			    }
			}
		    }
		}
		// удаляем связь на кого смотрел он
		if ($neuronet[$i]->output_to > -1) {
		    $neuronet[$neuronet[$i]->output_to]->maxinputs++;
		    $delid = $neuronet[$neuronet[$i]->output_to]->FindFrom($i);
		    array_splice($neuronet[$neuronet[$i]->output_to]->inputs, $delid, 1);
		    $neuronet[$i]->output_to = -1;
		}

		$neuronet[$i]->dead = true;


	    }
	}

	// split cell
	// echo "split\n";
	for ($i = 0; $i < count($neuronet); $i++) {
	    if ($neuronet[$i]->dead) continue;
	    if ($neuronet[$i]->type != 'summator'
		&& $neuronet[$i]->type != 'sensor'
		&& $neuronet[$i]->lifetime < 1
		&& $neuronet[$i]->energy >= 2
	    ) {
		// делим
		$olde = $neuronet[$i]->energy;
		$neuronet[$i]->energy = round($neuronet[$i]->energy / 2);
		$neuronet[$i]->lifetime = 10;
		$newenergy = $olde - $neuronet[$i]->energy;
		// создаем новую клетку
		echo "newborn!";
		if (!CreateNeuro($neuronet, $newenergy)) {
		    echo "error born!!";
		    //  exit;
		};
	    }
	}

	echo "exe $exebition\n";
	//  echo "exe path\n";
	for ($i = 0; $i < count($neuronet); $i++) {
	    // zero states
	    $neuronet[$i]->state = 0;
	    if ($neuronet[$i]->type != 'summator'
		&& $neuronet[$i]->type != 'sensor'
	    ) {
	    }
	}

	//   print_r($neuronet);
    }

    echo "try = $trys exe  suc\n";
    print_r($suc_exe);
    print_r($summas);
    /*
    for ($i = 0; $i < count($neuronet); $i++) {
        if ($neuronet[$i]->dead) continue;
        echo "\n$i ";
        print_r($neuronet[$i]);
    }
*/

    //  print_r($path);

    $su = 0;
    foreach ($suc_exe as $tmp) {
	if ($tmp) $su++;
    }
    if ($su == 4) {
	echo "allgood!";
	exit;
    }

    if (!count($path)) {
	echo "allbad!";
	print_r($neuronet);
	exit;
    }

}
for ($i = 0; $i < count($neuronet); $i++) {
    if ($neuronet[$i]->dead) continue;
    echo "\n$i ";
    print_r($neuronet[$i]);
}
print_r($path);