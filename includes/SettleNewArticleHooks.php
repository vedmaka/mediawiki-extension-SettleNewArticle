<?php

class SettleNewArticleHooks {

    /**
     * @param SFFormField $form_field
     * @param string $cur_value_in_template
     * @param bool $submited
     */
    public static function onsfCreateFormField( &$form_field, &$cur_value_in_template, $submited ) {

        global $wgOut;

        //TODO: disable title modifications after save

        if(!$submited) {
            if( $form_field->getInputName() == 'Card[Title]' ) {

                if( $wgOut->getRequest()->getVal('newarticle') ) {

                    #$form_field->setInputType('hidden');
                    #$form_field->setFieldArg('input type', 'hidden');
                    #$form_field->setFieldArg('disabled', 'disabled');
                    #$form_field->setIsHidden(true);
                    $form_field->setIsRestricted(true);

                }

            }
        }

    }

}