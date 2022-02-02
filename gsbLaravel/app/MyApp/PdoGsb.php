<?php
namespace App\MyApp;
use PDO;
use Illuminate\Support\Facades\Config;
class PdoGsb{
        private static $serveur;
        private static $bdd;
        private static $user;
        private static $mdp;
        private  $monPdo;
	
/**
 * crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	public function __construct(){
        
        self::$serveur='mysql:host=' . Config::get('database.connections.mysql.host');
        self::$bdd='dbname=' . Config::get('database.connections.mysql.database');
        self::$user=Config::get('database.connections.mysql.username') ;
        self::$mdp=Config::get('database.connections.mysql.password');	  
        $this->monPdo = new PDO(self::$serveur.';'.self::$bdd, self::$user, self::$mdp); 
		$this->monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		$this->monPdo =null;
	}
	

/**
 * Retourne les informations d'un visiteur

 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
	public function getInfosVisiteur($login, $mdp){
		$rs = $this->monPdo->query("SELECT visiteur.id AS id, visiteur.nom AS nom, visiteur.prenom AS prenom, visiteur.role AS role FROM visiteur WHERE visiteur.login='" . $login . "' and visiteur.mdp='" . $mdp ."'");
		$ligne = $rs->fetch();
		return $ligne;
	}
	public function getRole($login, $mdp)
	{
		$rs = $this->monPdo->query("SELECT visiteur.id AS id, visiteur.nom AS nom, visiteur.prenom AS prenom FROM visiteur WHERE visiteur.login='comptable' AND visiteur.mdp='1234'");
		$ligne = $rs->fetch();
		return $ligne;
	}
	public function getAllMonths()
	{
		$req = "SELECT DISTINCT fichefrais.mois AS mois FROM fichefrais";
		$rs = $this->monPdo->query($req);
		$ligne = $rs->fetchAll();
		return $ligne;
	}
	public function getAllVisiteurs()
	{
		$rs = $this->monPdo->query("SELECT DISTINCT visiteur.id AS id, visiteur.nom AS nom, visiteur.prenom AS prenom FROM visiteur WHERE role = 0 ORDER BY id ASC");
		$ligne = $rs->fetchAll();
		return $ligne;
	}
	// prepared (no SQL injection)
	public function getListNonRembourse($idV, $mois)
	{
		$rs = $this->monPdo->prepare("SELECT fichefrais.idVisiteur, mois, montantValide, idEtat FROM fichefrais WHERE idEtat != 'RB' AND idVisiteur = :idV AND mois = :mois ORDER BY idVisiteur");
		$rs->bindParam(":idV", $idV);
		$rs->bindParam(":mois", $mois);
		$rs->execute();
		$ligne = $rs->fetchAll();
		return $ligne;
	}
	// update the non refunded visiters
	public function updateListNonRembousees($idV, $mois)
	{
		$rs = $this->monPdo->prepare("UPDATE fichefrais SET idEtat = 'RB'  WHERE idVisiteur = :idV AND mois = :mois");
		$rs->bindParam(":idV", $idV);
		$rs->bindParam(":mois", $mois);
		$rs->execute();
	}





/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments

 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfait($idVisiteur, $mois){
		$req ="SELECT fraisforfait.id AS idfrais, fraisforfait.libelle AS libelle, 
		lignefraisforfait.quantite AS quantite FROM lignefraisforfait INNER JOIN fraisforfait 
		ON fraisforfait.id = lignefraisforfait.idfraisforfait
		WHERE lignefraisforfait.idvisiteur ='$idVisiteur' AND lignefraisforfait.mois='$mois' 
		ORDER BY lignefraisforfait.idfraisforfait";	
		$res = $this->monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait

 * @return un tableau associatif 
*/
	public function getLesIdFrais() {
		$req = "SELECT fraisforfait.id AS idfrais FROM fraisforfait ORDER BY fraisforfait.id";
		$res = $this->monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait

 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants

 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return un tableau associatif 
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$this->monPdo->exec("UPDATE lignefraisforfait SET lignefraisforfait.quantite = $qte
			WHERE lignefraisforfait.idvisiteur = '$idVisiteur' AND lignefraisforfait.mois = '$mois'
			AND lignefraisforfait.idfraisforfait = '$unIdFrais'");
		}
	}

/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument

 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		$ok = false;
		$res = $this->monPdo->query("SELECT count(*) AS nblignesfrais FROM fichefrais 
		WHERE fichefrais.mois = '$mois' AND fichefrais.idvisiteur = '$idVisiteur'");
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = true;
		}
		return $ok;
	}
/**
 * Retourne le dernier mois en cours d'un visiteur

 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idVisiteur){
		$res = $this->monPdo->query("SELECT max(mois) AS dernierMois FROM fichefrais WHERE fichefrais.idvisiteur = '$idVisiteur'");
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés

 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');
				
		}
		$this->monPdo->exec("INSERT INTO fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		VALUES('$idVisiteur','$mois',0,0,NOW(),'CR')");
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$this->monPdo->exec("INSERT INTO lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			VALUES('$idVisiteur','$mois','$unIdFrais',0)");
		}
	}


/**
 * Retourne les mois pour lesquel un visiteur a une fiche de frais

 * @param $idVisiteur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
	public function getLesMoisDisponibles($idVisiteur){
		$req = "SELECT fichefrais.mois AS mois FROM  fichefrais WHERE fichefrais.idvisiteur ='$idVisiteur' 
		ORDER BY fichefrais.mois DESC ";
		$res = $this->monPdo->query($req);
		$lesMois =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$numAnnee =substr( $mois,0,4);
			$numMois =substr( $mois,4,2);
			$lesMois["$mois"]=array(
			"mois"=>"$mois",
			"numAnnee"  => "$numAnnee",
			"numMois"  => "$numMois"
            );
			$laLigne = $res->fetch(); 		
		}
		return $lesMois;
	}
/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné

 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idVisiteur,$mois){
		$req = "SELECT fichefrais.idEtat AS idEtat, fichefrais.dateModif AS dateModif, fichefrais.nbJustificatifs AS nbJustificatifs, 
			fichefrais.montantValide AS montantValide, etat.libelle AS libEtat FROM  fichefrais INNER JOIN etat ON fichefrais.idEtat = etat.id 
			WHERE fichefrais.idvisiteur ='$idVisiteur' AND fichefrais.mois = '$mois'";
			//dd($req);
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
/**
 * Modifie l'état et la date de modification d'une fiche de frais

 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */

	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$rs = $this->monPdo->query("UPDATE ficheFrais SET idEtat = '$etat', dateModif = NOW() 
		WHERE fichefrais.idvisiteur ='$idVisiteur' AND fichefrais.mois = '$mois'");
		$this->monPdo->exec($req);
	}
}
