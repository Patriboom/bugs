var AllEditors = new Array();

function showckeditor (Quel, id) {
	CKEDITOR.config.entities = false;
	CKEDITOR.config.entities_latin = false;
	CKEDITOR.config.htmlEncodeOutput = false;

	AllEditors[id] = CKEDITOR.replace( Quel, {
		language: '<?php echo \Auth::user()->language; ?>',
		height: 175,
		toolbar : [
			{ name: 'Fichiers', items: ['Source']},
			{ name: 'CopieColle', items: ['Cut','Copy','Paste','PasteText','PasteFromWord','RemoveFormat']},
			{ name: 'FaireDefaire', items: ['Undo','Redo','-','Find','Replace','-','SelectAll']},
			{ name: 'Polices', items: ['Bold','Italic','Underline','TextColor']},
			{ name: 'ListeDec', items: ['horizontalrule','table','JustifyLeft','JustifyCenter','JustifyRight','Outdent','Indent','Blockquote']},
			{ name: 'Liens', items: ['Image', 'NumberedList','BulletedList','-','Link','Unlink']}
		]
	} );
}

function AffichonsEditor(id) {
	var CeComment = document.getElementById(id);
	var SesDiv = CeComment.childNodes;
	var SousDiv = SesDiv[1].childNodes;
	var SSousDiv = SousDiv[5].childNodes;
	setTimeout(function() {
		showckeditor (SSousDiv[1], id);
	} , 167);
}

function CachonsEditor(id) {
	return AllEditors[id].getData();
}

function ChangeonsEditor(id, contenu) {
	return AllEditors[id].setData(contenu);
}

function SupprimonsEditor(id) {
	AllEditors[id].destroy();
}
