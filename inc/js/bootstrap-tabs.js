$(
    function()
    {
        var hash = window.location.hash;
        hash && $( 'ul.nav a[href="' + hash + '"]' ).tab( 'show' );

        $( '.tab-links' ).click
        (
            function( e )
            {
                $( this ).tab( 'show' );
                
                var scrollmem = $( 'body' ).scrollTop() || $( 'html' ).scrollTop();
                window.location.hash = this.hash;
                $( 'html,body' ).scrollTop( scrollmem );
                
                $( 'ul.nav-tabs a[href="' + window.location.hash + '"]' ).tab( 'show' );
            }
        );
    }
);

