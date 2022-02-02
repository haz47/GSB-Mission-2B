<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PdoGsb;

class connexionController extends Controller
{
    function connecter(){
        
        return view('connexion')->with('erreurs',null);
    } 
    function valider(Request $request){
        $login = $request['login'];
        $mdp = $request['mdp'];
        $visiteur = PdoGsb::getInfosVisiteur($login,$mdp);
        // dd($visiteur);
        if(!is_array($visiteur)){
            $erreurs[] = "Login ou mot de passe incorrect(s)";
            return view('connexion')->with('erreurs',$erreurs);
        }
        elseif ($visiteur['role'] == 0) { // this checks if the role is 0, if it is then it's a visiter
            session(['visiteur' => $visiteur]);
            return view('sommaire')->with('visiteur',session('visiteur')); // this adds the visiter session to the current session
        }
        else {
            session(['visiteur' => $visiteur]); // else it's a comptable
            return view('sommaireC')->with('visiteur',session('visiteur')); // this adds the comptable session ON the visiter session, the session stays the same but adding compatble on it    
        }
    } 
    function deconnecter(){
            session(['visiteur' => null]);
            return redirect()->route('chemin_connexion');
    }
}
