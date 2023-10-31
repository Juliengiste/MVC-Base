<?php
/*******************************
 * Version : 1.0.0.0
 * Revised : jeudi 19 avril 2018, 16:29:28 (UTC+0200)
 *******************************/

namespace Core\Classes;

use DateTime;

class Utils
{

   /**
    * Parse recursively all user entrance to avoid html tags
    *
    * @param mixed $data Array or string to transform
    * @return string The string transformed.
    */
   private static function secure($data)
   {
      $result = "";

      if (is_array($data)) {
         $result = array();

         foreach ($data as $key => $value) {
            $result[$key] = self::secure($value);
         }

         return $result;
      }

      return strip_tags($data);
   }

   /**
    * Check if referer is the one expect or no one (good) vs different referer (forged request)
    *
    * @return boolean
    */
   public static function secureReferer()
   {
      if (DEBUG == 1) return true; // en local ou debug, pas de check
      if (stripos($_SERVER["HTTP_REFERER"], PROTOCOLE . "://" . URL) === 0 || strlen($_SERVER["HTTP_REFERER"]) == 0) return true;
      else return false;
   }

   public static function securePost($param, $default = null)
   {
      if (isset($_POST[$param])) {
         return self::secure($_POST[$param]);
      }

      return $default;
   }

   public static function secureGet($param, $default = null)
   {
      if (isset($_GET[$param])) {
         return self::secure($_GET[$param]);
      }
      return $default;
   }

   public static function secureRequest($param, $default = null)
   {
      if (isset($_REQUEST[$param])) {
         return self::secure($_REQUES[$param]);
      }
      return $default;
   }

   public static function secureFullPost($default = null)
   {
      if (isset($_POST)) {
         return self::secure($_POST);
      }

      return $default;
   }

   public static function secureFullGet($default = null)
   {
      if (isset($_GET)) {
         return self::secure($_GET);
      }
      return $default;
   }

   public static function secureFullRequest($default = null)
   {
      if (isset($_GET)) {
         return self::secure($_REQUEST);
      }
      return $default;
   }

   /**
    * Generate random password
    *
    * @param integer $param The lenght of the password.
    * @return string The generated password.
    */
   public static function genpwd($param)
   { // Génération aléatoire de mot de passe
      for ($s = '', $i = 0, $z = strlen($a = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') - 1; $i != $param; $x = rand(0, $z), $s .= $a{$x}, $i++) ;
      return $s;
   }

   /**
    * Replace all ponctuation and accent characters in a string by -
    *
    * @param string $param The string to transform
    * @return string The string transformed.
    */
   public static function noaccent($param, $remp = "-")
   { // formatage des accentués
      $rempchar = $t = $new = null;
      for ($i = 0; $i < 16; $i++) $rempchar .= $remp;

      $param = strtolower(trim(strtr(utf8_decode($param), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ\'" %?&/.():,;_°'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY' . $rempchar), $remp));
      for ($i = 0; $i < strlen($param); $i++) { // supprime les doublons de '-'
         if (($param[$i] != $t) || ($param[$i] != '-')) $new .= $param[$i];
         $t = $param[$i];
      }
      return $new;
   }

   /**
    * Handle Error correctly (no display in production)
    *
    * @param string $param The clear explanation
    * @param string $level 0 : fatal error ; 1 : warning ; 2 : notice
    * @return string The reason
    */
   public static function handleError($param = '', $level = 1)
   {
      $callers = debug_backtrace();
      $callers = array_reverse($callers);
      $sortie = $param . ' : <br><br>';
      foreach ($callers as $key => $caller) {
         $sortie .= ($key + 1) . '/ dans la fonction <strong>' . $caller['function'] . '()</strong> appelé depuis le fichier <strong>' . $caller['file'] . '</strong> à la ligne <strong>' . $caller['line'] . '</strong><br>';
      }
      // $paramst=self::noaccent(strip_tags($param),' ');
      error_log(strip_tags(strtr($sortie,
         'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
         'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY')));
      /*if ($level == 0) {
          if (DEBUG == 1) return $sortie;// exit($sortie);
          // else exit;
      } else {
          if (DEBUG == 1) trigger_error($sortie, $level);
      }*/
   }

   /**
    * Handle Time debug
    *
    * @return string Formated string with time of rendering
    */
   public static function debugTime()
   {
      global $timestamp_debut;

      if (DEBUG == 1) echo 'Exécution du script : ' . sprintf("%03.2f", ((microtime(true) - $timestamp_debut) * 1000)) . ' ms.';
   }

   /**
    * Ajoute un header à la page
    * @param $header
    */
   public static function formatDate($datetime, $format = false)
   {
      if (is_null($datetime)) Utils::handleError('Utils::formatDate : Pas de date à formater', 'E_CORE_NOTICE');

      if (strlen($datetime) == 10) list($Y, $m, $d) = explode("-", $datetime);
      else {
         list($Y, $m, $d) = explode("-", substr($datetime, 0, 10));
         list($H, $i, $s) = explode(":", substr($datetime, 11, strlen($datetime)));
      }

      if ($format == "Ymd") return $Y . $m . $d;
      //par défaut retour d/m/Y
      return $d . "/" . $m . "/" . $Y;
   }


   /**
    * Faire une recherche in_array dans un tableau mulidimensionnel
    * @param $needle : ce que l'on cherche
    * @param $haystack : le tableau multi
    * @param bool|false $strict : permet de vérifier une égalité parfaite
    * @return bool
    */
   public static function in_array_r($needle, $haystack, $strict = false)
   {
      foreach ($haystack as $item) {
         if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
            return true;
         }
      }

      return false;
   }

   /**
    * Supprime les doublons dans un tableau multidimensionnel
    * @param $array
    * @param $key
    * @return array
    */
   public static function array_unique_r($array, $key)
   {
      $temp_array = array();
      $i = 0;
      $key_array = array();

      foreach ($array as $val) {
         if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
         }
         $i++;
      }
      return $temp_array;
   }

   /**
    * Couper une chaine proprement à la fin d'un mot selon longueur renseignée
    * @param $text
    * @param $size
    * @return string
    */
   public static function cleanCut($text, $size)
   {
      if (strlen($text) >= $size) {
         $text = substr($text, 0, $size);
         $espace = strrpos($text, " ");
         $text = substr($text, 0, $espace);// . '...';
      }
      return $text;
   }

   public static function validateDate($date, $format = 'Y-m-d H:i:s')
   {
      $d = DateTime::createFromFormat($format, $date);
      return $d && $d->format($format) == $date;
   }

   /**
    * Vérifie si un array est vide récursivement
    * @param $InputVariable
    * @return bool
    */
   public static function is_array_empty($InputVariable)
   {
      $Result = true;

      if (is_array($InputVariable) && count($InputVariable) > 0) {
         foreach ($InputVariable as $Value) {
            $Result = $Result && self::is_array_empty($Value);
         }
      } else {
         $Result = empty($InputVariable);
      }

      return $Result;
   }

  /**
   * Fonction de pagination
   * @param int $nb : n° de la page à afficher
   * @param boolean $display : affichage du message "aucun résultat"
   */
  public static function displayPagination($nb=1,$display=0){
    if($_SESSION['nbresultats']['nbres']>0){
      $limitbas=($nb-4>=1)?$nb-3:2;
      $limithaut=($nb+3<$_SESSION['nbresultats']['nbpages'])?$nb+3:$_SESSION['nbresultats']['nbpages']-1;

      echo '<nav aria-label="Pagination des résultats">';
      echo '  <ul class="pagination pagination-sm justify-content-end flex-wrap">';
      // chevron
      echo '    <li class="page-item '.($nb==1?'disabled':'').'">';
      echo '      <a href="resultat?nb='.($nb-1>0?$nb-1:1).'" class="page-link" aria-label="Précédent">';
      echo '        <span aria-hidden="true">&laquo;</span>';
      echo '        <span class="sr-only">Précédent</span>';
      echo '      </a>';
      echo '    </li>';
      // start
      echo '<li class="page-item'.(1==$nb?' active':'').'"><a href="resultat?nb=1" class="page-link">1</a></li>';
      // espace
      if($limitbas!=2) echo '<li class="page-item disabled"><a href="#" class="page-link">...</a></li>';
      // 1st part
      for ($i=$limitbas; $i <= $nb ; $i++) {
        echo '<li class="page-item'.($i==$nb?' active':'').'"><a href="resultat?nb='.$i.'" class="page-link">'.$i.'</a></li>';
      }
      // 2nd part
      for ($i=$nb+1; $i <= $limithaut ; $i++) {
        echo '<li class="page-item'.($i==$nb?' active':'').'"><a href="resultat?nb='.$i.'" class="page-link">'.$i.'</a></li>';
      }
      // espace
      if($limithaut+1!=$_SESSION['nbresultats']['nbpages']) echo '<li class="page-item disabled"><a href="#" class="page-link">...</a></li>';
      // end
      if($nb!=$_SESSION['nbresultats']['nbpages'])echo '<li class="page-item'.($_SESSION['nbresultats']['nbpages']==$nb?' active':'').'"><a href="resultat?nb='.$_SESSION['nbresultats']['nbpages'].'" class="page-link">'.$_SESSION['nbresultats']['nbpages'].'</a></li>';
      // chevron
      echo '    <li class="page-item '.($nb==$_SESSION['nbresultats']['nbpages']?'disabled':'').'">';
      echo '      <a href="resultat?nb='.($nb+1<=$_SESSION['nbresultats']['nbpages']?$nb+1:$_SESSION['nbresultats']['nbpages']).'" class="page-link" aria-label="Précédent">';
      echo '        <span aria-hidden="true">&raquo;</span>';
      echo '        <span class="sr-only">Suivant</span>';
      echo '      </a>';
      echo '    </li>';
      echo '  </ul>';
      echo '</nav>';
    }
    elseif($display!=0) echo "Aucun résultat ne correspond à votre demande";
  }

  /**
   * Search and Define default language
   *
  */
  public static function defaultLocalization(){
    global $lang;

    $param=array(
      "ask"     =>  Utils::secureGet("lg"),
      "session_core"  =>  $_SESSION[SHORTNAME."lg_core"],
      "session_app" =>  $_SESSION[SHORTNAME."lg_app"],
      "nav"     =>  strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2))
    );
    unset($lg_app);
    unset($lg_core);

    // Core
    foreach ($param as $value) {
      if(is_file(DR."Core/localization/".$value."/_common.inc.php")) {
        $lg_core=$value;
        break;
      }
    }
    if($lg_core)$_SESSION[SHORTNAME."lg_core"]=$lg_core;
    else$_SESSION[SHORTNAME."lg_core"]=CORE_DEFAULT_LANGUAGE;

    // App
    foreach ($param as $value) {
      if(is_file(DR."template/localization/".$value."/_common.inc.php")) {
        $lg_app=$value;
        break;
      }
    }
    if($lg_app)$_SESSION[SHORTNAME."lg_app"]=$lg_app;
    else$_SESSION[SHORTNAME."lg_app"]=APP_DEFAULT_LANGUAGE;

    require_once(DR.'Core/localization/'.$_SESSION[SHORTNAME."lg_core"].'/_common.inc.php');
    require_once(DR.'template/localization/'.$_SESSION[SHORTNAME."lg_app"].'/_common.inc.php');
  }

}