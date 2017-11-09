( function( $, wp, _, rules ) {
	var fieldTmpl = wp.template( 'csstidy-rule-field' ),
		$rules = $('#csstidy-custom-rules');

	$rules.html('');
	_.each( rules, function( rule ) {
		if ( ! rule.name ) {
			rule.name =  'new-' + Date.now() + '-' + Math.random();
		}
		$rules.append( fieldTmpl( rule ) );
	} );

	$('.add-new-rule').on('click', function(e){
		e.preventDefault();
		$rules.append( fieldTmpl( {
			name : 'new-' + Date.now() + '-' + Math.random(),
			rule : '',
			versions : [
				'CSS1.0',
				'CSS2.0',
				'CSS2.1',
				'CSS3.0'
			]
		} ) );
	});

	if ( 0 === rules.length ) {
		$('.add-new-rule').first().click();
	}

	$rules.on( 'click', '.trash a', function(e){
		e.preventDefault();
		$(e.target).closest('li').remove();
	});

}( jQuery, wp, _, csstidyCustomRules.rules ) );