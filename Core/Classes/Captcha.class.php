<?
/*******************************
Version : 1.0.1.1
Revised : mardi 4 juillet 2017, 19:01:04 (UTC+0200)
*******************************/

namespace Core\Classes;

class Captcha {
	/*** Configuration ***/
	
	// fond
	static private $sicaptchawidth  = 165;  // Largeur du cryptogramme (en pixels)
	static private $sicaptchaheight = 37;   // Hauteur du cryptogramme (en pixels)
	static private $bgR  = 255;         // Couleur du fond au format RGB: Red (0->255)
	static private $bgG  = 255;         // Couleur du fond au format RGB: Green (0->255)
	static private $bgB  = 255;         // Couleur du fond au format RGB: Blue (0->255)
	static private $bgclear = false;	// Fond transparent (true/false), uniquement valable pour le format PNG
	static private $bgimg = '';		// Le fond du cryptogramme peut-être une image PNG, GIF ou JPG. L'image sera redimensionnée si nécessaire pour tenir dans le cryptogramme.
								// Si vous indiquez un répertoire plutôt qu'un fichier l'image sera prise au hasard parmi celles disponibles dans le répertoire
	static private $bgframe = true;	// Ajoute un cadre de l'image (true/false)
	static private $bgcol 	= true;	// true : couleur des caractères, false : atténuation de la couleur des caractères

	// caractères
	static private $charR = 166;     // Couleur des caractères au format RGB: Red (0->255)
	static private $charG = 166;     // Couleur des caractères au format RGB: Green (0->255)
	static private $charB = 166;     // Couleur des caractères au format RGB: Blue (0->255)

	static private $charcolorrnd = true;	// Choix aléatoire de la couleur.
	static private $charcolororbnw = false;	// Choix entre couleur et nuance de gris.
	static private $charcolorrndlevel = 2;	// Niveau de clarté des caractères si choix aléatoire (0->4)
									// 0: Aucune sélection
									// 1: Couleurs très sombres (surtout pour les fonds clairs)
									// 2: Couleurs sombres
									// 3: Couleurs claires
									// 4: Couleurs très claires (surtout pour fonds sombres)

	static private $charclear = 20;	// Intensité de la transparence des caractères (0->127)
									// 0=opaques; 127=invisibles

	// Polices de caractères
	static private $font = 'fonts/luggerbu.ttf';

	// Caracteres autorisés
	static private $charel = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';       // Caractères autorisés

	static private $sicaptchaeasy = false;	// Création de cryptogrammes "faciles à lire" (true/false)
						// composés alternativement de consonnes et de voyelles.

	static private $charelc = 'BCDFGHJKLMNPQRSTVWXZ';   // Consonnes utilisées si $sicaptchaeasy = true
	static private $charelv = 'AEOUY';              // Voyelles utilisées si $sicaptchaeasy = true

	static private $difuplow = false;          // Différencie les Maj/Min lors de la saisie du code (true, false)

	static private $charnbmin = 5;         // Nb minimum de caracteres dans le cryptogramme
	static private $charnbmax = 5;         // Nb maximum de caracteres dans le cryptogramme

	static private $charspace = 32;        // Espace entre les caracteres (en pixels)
	static private $charsizemin = 25;      // Taille minimum des caractères
	static private $charsizemax = 30;      // Taille maximum des caractères

	static private $charanglemax  = 15;     // Angle maximum de rotation des caracteres (0-360)
	static private $charup   = true;        // Déplacement vertical aléatoire des caractères (true/false)

	// Effets supplémentaires
	static private $sicaptchagaussianblur = false; // Transforme l'image finale en brouillant: méthode Gauss (true/false)
	static private $sicaptchagrayscal = false;     // Transforme l'image finale en dégradé de gris (true/false)

	// Configuration du bruit
	static private $noisepxmin = 0;      // Bruit: Nb minimum de pixels aléatoires
	static private $noisepxmax = 500;      // Bruit: Nb maximum de pixels aléatoires

	static private $noiselinemin = 0;     // Bruit: Nb minimum de lignes aléatoires
	static private $noiselinemax = 2;     // Bruit: Nb maximum de lignes aléatoires

	static private $nbcirclemin = 0;      // Bruit: Nb minimum de cercles aléatoires 
	static private $nbcirclemax = 3;      // Bruit: Nb maximim de cercles aléatoires

	static private $noisecolorchar  = 1;  // Bruit: Couleur d'ecriture des pixels, lignes, cercles: 
						   // 1: Couleur d'écriture des caractères
						   // 2: Couleur du fond
						   // 3: Couleur aléatoire
						   
	static private $brushsize = 1;        // Taille d'ecriture du princeaiu (en pixels) de 1 à 25
	static private $noiseup = true;      // Le bruit est-il par dessus l'ecriture (true) ou en dessous (false) 

	// Configuration système & sécurité
	static private $sicaptchaformat = "png";   // Format du fichier image généré "GIF", "PNG" ou "JPG"
									// Si vous souhaitez un fond transparent, utilisez "PNG" (et non "GIF")

	static private $sicaptchausetimer = 30;        // Temps (en seconde) avant d'avoir le droit de regénérer un cryptogramme

	static private $sicaptchausertimererror = 3;  // Action à réaliser si le temps minimum n'est pas respecté:
							   // 1: Ne rien faire, ne pas renvoyer d'image.
							   // 2: L'image renvoyée est "images/erreur2.png" (vous pouvez la modifier)
							   // 3: Le script se met en pause le temps correspondant (attention au timeout
							   //    par défaut qui coupe les scripts PHP au bout de 30 secondes)
							   //    voir la variable "max_execution_time" de votre configuration PHP

	static private $sicaptchausemax = 1000;	// Nb maximum de fois que l'utilisateur peut générer le cryptogramme
								// Si dépassement, l'image renvoyée est "images/erreur1.png"
						  
	static private $sicaptchaneuse = false;  // Si vous souhaitez que la page de verification ne valide qu'une seule 
						   // fois la saisie en cas de rechargement de la page indiquer "true".
						   // Sinon, le rechargement de la page confirmera toujours la saisie.                          

	/*** Variables ***/
	static private $img;
	static private $bg;
	static private $xvariation;
	static private $tword;
	static private $charnb;
	static private $ink;
	
	/*** Fonctions ***/

	// Affiche le captcha
	static public function displayCaptcha($reload=0) {
		echo '<img id="icaptcha" src="'.self::generateCaptcha(1).'">';
		// if ($reload) echo '<a title="'.($reload==1?'':$reload).'" onmouseover="$(this).css({\'cursor\':\'pointer\'})" onclick="$(\'#icaptcha\').attr(\'src\',\'kernel/lib/captcha/captcha.php?\'+Math.round(Math.random(0)*1000)+1)"> <img src="kernel/lib/captcha/images/reload.png"></a>';
	}

	// Check le captcha
	static public function checkCaptcha($code) {
		$code = trim($code);
		$code = self::$difuplow?$code:strtoupper($code);
		$code = hash('sha512',$code);
	
		if (isset($_SESSION['sicaptchacode']) && $_SESSION['sicaptchacode'] == $code){
			unset($_SESSION['sicaptchareload']);
			if ($sicaptchaneuse) unset($_SESSION['sicaptcha']);    
			return true;
		}
		else {
			$_SESSION['sicaptchareload']= true;
			return false;
		}
	}
	
	static public function generateCaptcha($inimg=0){
		// détermination chemin en fonction du rendu
		if($inimg)self::$font="Core/Classes/".self::$font;
		else self::$font="./".self::$font;
		
		// dépassement du nb max de rechargement
		if ($_SESSION['sicaptchause']>=self::$sicaptchausemax) {
			return 'Core/Classes/images/erreur1.png'; 
			exit;
		}
		
		// rechargement trop rapide
		$delai = time()-$_SESSION['sicaptchatime'];
		if ($delai < $sicaptchausetimer) { 
			if($sicaptchausertimererror==2) {
				header("Content-type: image/png");
				readfile('images/erreur2.png'); 
				exit;
			}
			elseif($sicaptchausertimererror==3) {
				sleep($sicaptchausetimer-$delai);
			}
		}
		
		// Création de la ligne de caractère
		$word ='';
		$pair = rand(0,1);
		self::$charnb = rand(self::$charnbmin,self::$charnbmax);
		for($i=1;$i<=self::$charnb;$i++) {              
			self::$tword[$i]['angle'] = (rand(1,2)==1)?rand(0,self::$charanglemax):rand(360-self::$charanglemax,360);
			 
			if ($sicaptchaeasy) self::$tword[$i]['element'] =(!$pair)?self::$charelc{rand(0,strlen(self::$charelc)-1)}:self::$charelv{rand(0,strlen(self::$charelv)-1)};
			else self::$tword[$i]['element'] = self::$charel{rand(0,strlen(self::$charel)-1)};

			$pair=!$pair;
			self::$tword[$i]['size'] = rand(self::$charsizemin,self::$charsizemax);
			self::$tword[$i]['y'] = (self::$charup?(self::$sicaptchaheight/1.2)+rand(0,(self::$sicaptchaheight/6)):(self::$sicaptchaheight/1.1));
			$word.=self::$tword[$i]['element'];
		}

		// Création du fond
		self::$img = imagecreatetruecolor(self::$sicaptchawidth,self::$sicaptchaheight); 

		if(self::$bgimg && is_dir(self::$bgimg)){
			$dh=opendir(self::$bgimg);
			while(false!==($filename = readdir($dh)))if(eregi(".[gif|jpg|png]$", $filename))$files[] = $filename;
			closedir($dh);
			self::$bgimg=self::$bgimg.'/'.$files[array_rand($files,1)];
		}
		elseif(self::$bgimg){
			list($getwidth, $getheight, $gettype, $getattr) = getimagesize(self::$bgimg);
			switch ($gettype) {
				case "1": $imgread = imagecreatefromgif(self::$bgimg); break;
				case "2": $imgread = imagecreatefromjpeg(self::$bgimg); break;
				case "3": $imgread = imagecreatefrompng(self::$bgimg); break;
			}
			imagecopyresized(self::$img, $imgread, 0,0,0,0,self::$sicaptchawidth,self::$sicaptchaheight,$getwidth,$getheight);
			imagedestroy($imgread);
		}
		else {
			self::$bg=imagecolorallocate(self::$img,self::$bgR,self::$bgG,self::$bgB);
			imagefill(self::$img,0,0,self::$bg);
			if($bgclear)imagecolortransparent(self::$img,self::$bg);
		}
		
		// Création de l'écriture
		if($noiseup){self::ecriture();self::bruit();}
		else{self::bruit();self::ecriture();}
		
		// Création du cadre
		if(self::$bgframe){
			if(self::$bgcol) $framecol=imagecolorallocate(self::$img,self::$charR,self::$charG,self::$charB);
			else $framecol=imagecolorallocate(self::$img,(self::$bgR*3+self::$charR)/4,(self::$bgG*3+self::$charG)/4,(self::$bgB*3+self::$charB)/4);
			imagerectangle(self::$img,0,0,self::$sicaptchawidth-1,self::$sicaptchaheight-1,$framecol);
		}
		
		// Transformations supplémentaires: Grayscale et Brouillage
		if(function_exists('imagefilter')){
			if(self::$sicaptchagrayscal) imagefilter(self::$img,IMG_FILTER_GRAYSCALE);
			if(self::$sicaptchagaussianblur) imagefilter(self::$img,IMG_FILTER_GAUSSIAN_BLUR);
		}

		// Conversion du cryptogramme en Majuscule si insensibilité à la casse
		$word=self::$difuplow?$word:strtoupper($word);

		$_SESSION['sicaptchacode']=hash('sha512',$word);
		$_SESSION['sicaptchatime']=time();
		$_SESSION['sicaptchause']++;       

		// Envoi de l'image finale au navigateur 
		switch (strtoupper(self::$sicaptchaformat)) {  
			case "JPG"  :
			case "JPEG" : if (imagetypes() & IMG_JPG)  {
							ob_start();
							imagejpeg(self::$img, "", 75);
							$i=ob_get_clean();
							if($inimg)return "data:image/jpg;base64,".base64_encode($i);
							else {
								header("Content-type:image/jpg");
								return $i;
							}
						  }
						  break;
			case "GIF"  : if (imagetypes() & IMG_GIF)  {
							ob_start();
							imagegif(self::$img);
							$i=ob_get_clean();
							if($inimg)return "data:image/gif;base64,".base64_encode($i);
							else {
								header("Content-type:image/gif");
								return $i;
							}
						  }
						  break;
			case "PNG"  : 
			default     : if (imagetypes() & IMG_PNG)  {
							ob_start();
							imagepng(self::$img);
							$i=ob_get_clean();
							if($inimg)return "data:image/png;base64,".base64_encode($i);
							else {
								header("Content-type:image/png");
								return $i;
							}
						  }
		}

		imagedestroy(self::$img);
		unset($word,$tword);
		unset($_SESSION['sicaptchareload']);
	}
	
	// Création de l'écriture
	static private function ecriture(){
		if (function_exists ('imagecolorallocatealpha')) self::$ink = imagecolorallocatealpha(self::$img,self::$charR,self::$charG,self::$charB,self::$charclear);
		else self::$ink = imagecolorallocate (self::$img,self::$charR,self::$charG,self::$charB);

		$x = 5;
		for ($i=1;$i<=self::$charnb;$i++) {       
			if (self::$charcolorrnd){   // Choisit des couleurs au hasard
				$ok = false;
				while(!$ok){
					if(self::$charcolororbnw) {
						$rndR = rand(0,255); $rndG=$rndR; $rndB=$rndR;
					}
					else {
						$rndR = rand(0,255); $rndG = rand(0,255); $rndB = rand(0,255);
					}
					$rndcolor = $rndR+$rndG+$rndB;
					switch (self::$charcolorrndlevel) {
						case 1  : if ($rndcolor<200) $ok=true; break; // tres sombre
						case 2  : if ($rndcolor<400) $ok=true; break; // sombre
						case 3  : if ($rndcolor>500) $ok=true; break; // claires
						case 4  : if ($rndcolor>650) $ok=true; break; // très claires
						default : $ok=true;               
					}
				}
				  
				if(function_exists ('imagecolorallocatealpha')) $rndink = imagecolorallocatealpha(self::$img,$rndR,$rndG,$rndB,self::$charclear);
				else $rndink = imagecolorallocate(self::$img,$rndR,$rndG,$rndB);          
			}
			imagettftext(self::$img,self::$tword[$i]['size'],self::$tword[$i]['angle'],$x,self::$tword[$i]['y'],self::$charcolorrnd?$rndink:self::$ink,self::$font,self::$tword[$i]['element']);

			$x +=self::$charspace;
		} 
	}
	
	// Ajout de bruits: point, lignes et cercles aléatoires
	static private function bruit(){
		$nbpx = rand(self::$noisepxmin,self::$noisepxmax);
		$nbline = rand(self::$noiselinemin,self::$noiselinemax);
		$nbcircle = rand(self::$nbcirclemin,self::$nbcirclemax);
		for($i=1;$i<$nbpx;$i++) imagesetpixel(self::$img,rand(0,self::$sicaptchawidth-1),rand(0,self::$sicaptchaheight-1),self::noisecolor());
		for($i=1;$i<=$nbline;$i++) imageline(self::$img,rand(0,self::$sicaptchawidth-1),rand(0,self::$sicaptchaheight-1),rand(0,self::$sicaptchawidth-1),rand(0,$sicaptchaheight-1),self::noisecolor());
		for($i=1;$i<=$nbcircle;$i++) imagearc(self::$img,rand(0,self::$sicaptchawidth-1),rand(0,self::$sicaptchaheight-1),$rayon=rand(5,self::$sicaptchawidth/3),$rayon,0,360,self::noisecolor());
	}
	
	// Fonction permettant de déterminer la couleur du bruit et la forme du pinceau
	static private function noisecolor(){
		switch (self::$noisecolorchar) {
			case 1  : $noisecol=self::$ink; break;
			case 2  : $noisecol=self::$bg; break;
			case 3  : 
			default : $noisecol=imagecolorallocate (self::$img,rand(0,255),rand(0,255),rand(0,255)); break;               
		}
		if (self::$brushsize and self::$brushsize>1 and function_exists('imagesetbrush')) {
			$brush = imagecreatetruecolor(self::$brushsize,self::$brushsize);
			imagefill($brush,0,0,$noisecol);
			imagesetbrush(self::$img,$brush);
			$noisecol=IMG_COLOR_BRUSHED;
		}
		return $noisecol;    
	}

	// Retour d'une ligne de texte si "echo <classe>"
	public function __toString () {
		// self::displayCaptcha(1);
		return "Captcha !";
	}

}