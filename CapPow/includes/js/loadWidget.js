jQuery('document.body').ready(function($){
    
    //load cap widget if needed
    let formsToCheck    = (typeof cap_widget_params == 'object' && cap_widget_params.hasOwnProperty('formsToCheck')) ? cap_widget_params.formsToCheck : {};
    let CapPoWScript    = (typeof cap_widget_params == 'object' && cap_widget_params.hasOwnProperty('widget_src')) ? cap_widget_params.widget_src : 'https://cdn.jsdelivr.net/npm/cap-widget';
    let forceWidget     = (typeof cap_widget_params == 'object' && cap_widget_params.hasOwnProperty('forceWidget')) ? cap_widget_params.forceWidget : false;
    let formsLoaded     = '';

    for(let i in formsToCheck){
        console.log(`${i}: ${formsToCheck[i]}`);
        if($(i).length > 0){
            //log needed buttons selectors
            formsLoaded = (formsLoaded != '') ? `${formsLoaded}, ${formsToCheck[i]}` : formsToCheck[i];
        }
    }
    console.log(formsLoaded);
    if(formsLoaded != '' || forceWidget == true){
        $.getScript(CapPoWScript, function(){
            $(document.body).trigger('CapPoW');
            if(formsLoaded == ''){
                return;
            }
            let addCapToRegister = function(e, skip=false){

                if(skip == true){
                    return;
                }

                e.preventDefault();
                let form = $(this).parents('form:first');
                let button = this;
                const cap = new Cap({
                    apiEndpoint: cap_widget_params.api,
                });
                let token = cap.solve();
                token
                    .then(function(event){

                        if($('#cap-token').length > 0){
                            $('#cap-token').val(event.token);
                            return form.submit();
                        }

                        form.append(
                           $("<input>").attr({
                                type:   'hidden',
                                id:     'cap-token',
                                name:   'cap-token',
                                value:  event.token
                            })
                        );
                        $(button).trigger('click', [true]);
                    });
            }
            $(document.body).on('click', formsLoaded, addCapToRegister);
        });
    }

});