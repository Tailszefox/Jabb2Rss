<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="style.css" type="text/css">
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
		
		// Affiche ou cache la description du service
		$('#aproposTexte').click(function() {
				if($('#description:visible').length == 0)
					$('#aproposTexte').html('Fermer la description');
				else
					$('#aproposTexte').html('À propos de Jabb2Rss');
				
				$('#description').slideToggle();
		});
		
		// Rajoute un input pour une adresse de flux (nouvel utilisateur)
		$('.ajoutFlux').live('click', function () {
				$('#rss').append('<br /><input type="text" size="40" name="rss[]" class="adresseFlux" /> <span class="ajoutFlux">+</span>');
		});
		
		// Rajoute un input pour une adresse de flux (mise à jour)
		$('.ajoutFluxBis').live('click', function () {
				$('#rssBis').append('<br /><input type="text" size="40" name="rss[]" class="adresseFluxBis" /> <span class="ajoutFluxBis">+</span>');
		});
		
		// Vérification de la validité du flux entré
		$('.adresseFlux').live('blur', function () {
				
				element = $(this);
				element.css('color', 'gray');
				
				if($(this).val() != "")
				{
					// Appel de la page check_rss.php pour vérification
					$.post("check_rss.php", { url : $(this).val() },
						function(data){
							if(data == '0')
							{
								alert('L\'URL entré n\'est pas un flux RSS valide.');
								element.css('color', 'red');
								element.attr('valide', 'false');
							}
							else
							{
								element.css('color', 'black');
								element.attr('valide', 'true');
							}
						});
				}
				else
				{
					element.css('color', 'black');
					element.attr('valide', 'true');
				}
		});

		$('.adresseFluxBis').live('blur', function () {
				
				element = $(this);
				element.css('color', 'gray');
				
				if($(this).val() != "")
				{
					// Appel de la page check_rss.php pour vérification
					$.post("check_rss.php", { url : $(this).val() },
						function(data){
							if(data == '0')
							{
								alert('L\'URL entré n\'est pas un flux RSS valide.');
								element.css('color', 'red');
								element.attr('valide', 'false');
							}
							else
							{
								element.css('color', 'black');
								element.attr('valide', 'true');
							}
						});
				}
				else
				{
					element.css('color', 'black');
					element.attr('valide', 'true');
				}
		});
		
		// Enregistrement du formulaire d'inscription
		$('#formInscription').submit(function() {
				
			// Toutes les cases ne sont pas remplies
			if($('#formInscription input').eq(0).val() == "" || $('#formInscription input').eq(1).val() == "")
			{
				alert('Veuillez renseigner votre adresse et votre mot de passe.');
				return false;
			}
			
			adresse = $('#formInscription input').eq(0).val();
			motdepasse = $('#formInscription input').eq(1).val();
			
			// Vérification de la validité de l'adresse Jabber
			var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;  
			if(!pattern.test(adresse))
			{
				alert('Votre adresse est incorrecte. Veuillez la corriger.');
				return false;
			}
			
			// Il reste des flux RSS invalides
			if($('.adresseFlux[valide="false"]').length > 0)
			{
				alert('Un ou plusieurs flux RSS sont invalides. Veuillez les corriger.');
				return false;
			}
			
			// Création d'un tableau des flux RSS
			var rssArray = $('.adresseFlux').map(function(i,n) {
					return $(n).val();
			}).get();

			form = $(this);
			form.slideUp();
			
			// Inscription de l'utilisateur
			$.post("inscription.php", { adresse :  adresse, motdepasse : motdepasse, 'rss[]' : rssArray},
				function(data){
					form.html(data);
					form.slideDown();
				});
			
			return false;
		});
		
		// Demande de login
		$('#rssLogin').live('submit', function() {
			
			if($('.adresseFluxBis[valide="false"]').length > 0)
			{
				alert('Un ou plusieurs flux RSS sont invalides. Veuillez les corriger.');
				return false;
			}
			
			var rssArray = $('.adresseFluxBis').map(function(i,n) {
					return $(n).val();
			}).get();

			form = $(this);
			form.slideUp();
			$.post("modifier_rss.php", {'rss[]' : rssArray},
				function(data){
					form.html(data);
					form.slideDown();
				});
			
			return false;
		});
		
		// Modification des abonnements
		$('#formLogin').live('submit', function() {
			if($('#formLogin input').eq(0).val() == "" || $('#formLogin input').eq(1).val() == "")
			{
				alert('Veuillez renseigner votre adresse et votre mot de passe.');
				return false;
			}
			
			adresse = $('#formLogin input').eq(0).val();
			motdepasse = $('#formLogin input').eq(1).val();

			form = $(this);
			form.slideUp();
			$.post("liste_rss.php", { adresse :  adresse, motdepasse : motdepasse},
				function(data){
					form.html(data);
					form.attr('id', 'rssLogin');
					form.slideDown();
				});
			
			return false;
		});
		
		$('input[name="desinscription"]').live('click', function(){
				if(confirm('Voulez-vous vraiment vous désinscrire ?'))
				{
					$.post("desinscription.php", {},
						function(data){
							$('#rssLogin').html('<p>Vous avez bien été désinscrit :(</p>');
					});
				}
		});
})
</script>
<title>Jabb2Rss</title>
</head>
<body>

<div id="site">
<div id="logo">
	<span>Jabb2Rss</span>
</div>

<div id="description">
	<p>Jabb2Rss est un service vous permettant de recevoir directement sur votre adresse Jabber vos flux RSS. Une façon aisée de vous tenir informé en temps réel sans quitter votre client Jabber préféré.</p>
	<p>Le principe est très simple : il vous suffit d'entrer votre adresse, un mot de passe, et de lister les flux RSS que vous désirez suivre. Vous recevrez ensuite régulièrement les mises à jour en provenance de
	vos sites préférés.</p>
	<p>Une fois inscrit, vous pouvez à tout moment revenir sur ce site pour pouvoir modifier votre liste de flux RSS, ou vous désinscrire.</p>
</div>

<div id="apropos">
	<span id="aproposTexte">À propos de Jabb2Rss</span>
</div>

<div id="inscription">
	<form method="post" id="formInscription"> 
	<p>Inscription</p>
	<div class="left">
		<p>Votre adresse Jabber</p>
		<p>Votre mot de passe</p>
	</div>
	<div class="right">
		<p><input type="text" name="adresse" /></p>
		<p><input type="password" name="motdepasse" /></p>
	</div>
	
	<p>Vos flux RSS</p>
	<div id="rss">
	<input type="text" size="40" name="rss[]" class="adresseFlux" /> <span class="ajoutFlux">+</span>
	</div>
	
	<p><input type="submit" /></p>
	</form>
</div>
	
<div id="login">
	<form method="post" id="formLogin"> 
	<p>Identification</p>
	
	<div class="left">
		<p>Votre adresse Jabber</p>
		<p>Votre mot de passe</p>
	</div>
	<div class="right">
		<p><input type="text" name="adresse" /></p>
		<p><input type="password" name="motdepasse" /></p>
	</div>
	
	<p><input type="submit" /></p>
</div>
</div>
</body>
</html>
