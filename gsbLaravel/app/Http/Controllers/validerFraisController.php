<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PdoGsb;
use MyDate;
class validerFraisController extends Controller {
    function validerFrais(Request $request) {
        if(session('visiteur')!= null){ //Sans la session l’insertion du sommaire
            $visiteur = session('visiteur'); //provoque une erreur
           // dd($visiteur);
            $lesMois = PdoGsb::getAllMonths();
            $lesVisiteurs = PdoGsb::getAllVisiteurs();
            /* dd($lesVisiteurs); */
            $lesCles = array_keys($lesMois);
            $moisASelectionner = $lesCles[0];
            return view('listemoisC')
                        ->with('lesMois', $lesMois)
                        ->with('leMois', $moisASelectionner)
                        ->with('lesVisiteurs',$lesVisiteurs)
                        ->with('visiteur',$visiteur);
        }
    }
    function getListNonRembousees(Request $request) {
        if(session('visiteur')!= null){ //Sans la session l’insertion du sommaire
            $visiteur = session('visiteur'); //provoque une erreur
            //dd($request);
            $visiteurASelectionner = $request['lstVisiteurs'];
            $leMoisASelectionner = $request['lstMois'];
            $lesMois = PdoGsb::getAllMonths();
            $lesVisiteurs = PdoGsb::getAllVisiteurs();
            $lesRembourses = PdoGsb::getListNonRembourse($visiteurASelectionner, $leMoisASelectionner);
            $lesCles = array_keys($lesMois);
            $moisASelectionner = $lesCles[0];
            return view('listemoisCForm')
                        ->with('lesMois', $lesMois)
                        ->with('leMois', $moisASelectionner)
                        ->with('leVisiteur', $visiteurASelectionner)
                        ->with('lesVisiteurs',$lesVisiteurs)
                        ->with('lesRembourses',$lesRembourses)
                        ->with('visiteur',$visiteur);
        }
    }
    function updateListNonRembousees(Request $request) {
        if(session('visiteur')!= null) { //Sans la session l’insertion du sommaire
            $visiteur = session('visiteur'); //provoque une erreur
            if(isset($request['lstrembourse'])) {
                $lstRembourse=explode(" ", $request['lstrembourse']);
                $visiteurASelectionner = $lstRembourse[0];
                $leMoisASelectionner = $lstRembourse[1];
                $lesRembourses = PdoGsb::updateListNonRembousees($visiteurASelectionner, $leMoisASelectionner);
                return view('sommaireC')->with('visiteur', $visiteur);
            }
     
  
           
            // return view('message',$message);
        }
    }
}