<?php

/**
 *
 * Class SpecialSettleNewArticle
 */
class SpecialSettleNewArticle extends SpecialPage {

    private $templateParser;

    public function __construct($name = '', $restriction = '', $listed = true, $function = false, $file = '', $includable = false)
    {
        parent::__construct('SettleNewArticle');
    }

    public function execute($subPage)
    {
    	$this->getOutput()->addModules('ext.settlenewarticle.main');
    	$this->getOutput()->setPageTitle( wfMessage('settlenewarticle-title')->plain() );

        $this->templateParser = new TemplateParser( dirname(__FILE__).'/../templates/', true );

        if( $this->getRequest()->wasPosted() ) {
	        $step = $this->getRequest()->getVal('step');
	        switch ($step) {
		        case 1:
		        	// Category was selected, select a geo-scope values
					$this->displayStepOne();
		        	break;
	        }
        }else{
        	$this->displayMain();
        }

    }

    private function displayStepOne() {

    	global $wgLang;

    	$categoryId = $this->getRequest()->getInt('category_id');
    	$country = $this->getRequest()->getInt('country_id');
    	$state = $this->getRequest()->getInt('state_id');
    	$city = $this->getRequest()->getInt('city_id');

	    $category = new SettleGeoCategory( $categoryId );
	    $categoryScope = $category->getGeoScope();

	    $geoids = array();
	    if( $country ) {
	    	$geoids[] = $country;
	    }
	    if( $state ) {
	    	$geoids[] = $state;
	    }
	    if( $city ) {
	    	$geoids[] = $city;
	    }

	    // Fetch geo_entities names
	    $data = array(
		    'category_id' => $categoryId,
		    'category_name' => $category->getTitleKey(),
		    'category_scope' => $categoryScope,
		    'geo_ids' => implode(',', $geoids ),
		    'country_name' => false,
		    'state_name' => false,
		    'city_name' => false,
		    'proceed_url' => ''
	    );

	    try {
		    $earth = new MenaraSolutions\Geographer\Earth();
		    $entity = $earth->findOne( array('geonamesCode' => $country) );
		    $data['country_name'] = $entity->setLanguage( $wgLang->getCode() )->getShortName();
	    }catch (Exception $e) {
	    	// Nothing
	    }

	    try {
		    $entity = MenaraSolutions\Geographer\State::build( $state );
		    $data['state_name'] = $entity->setLanguage( $wgLang->getCode() )->getShortName();
	    }catch (Exception $e) {
		    // Nothing
	    }

	    try {
		    $entity = MenaraSolutions\Geographer\City::build( $state );
		    $data['city_name'] = $entity->setLanguage( $wgLang->getCode() )->getShortName();
	    }catch (Exception $e) {
		    // Nothing
	    }

	    // TODO: add form url
	    $data['proceed_url'] = '';

	    $html = $this->templateParser->processTemplate('step_1', $data);
	    $this->getOutput()->addHTML($html);

    }

    private function displayMain()
    {

    	$this->getOutput()->addModules('ext.settlegeocategories.input');
    	$this->getOutput()->addModules('skins.settlein.animate.standalone');

    	global $wgLanguageCode;

	    $data = array(
		    'categoriesHtml' => '',
		    'countriesHtml' => ''
	    );

	    $countries = SettleGeoTaxonomy::getInstance()->getEntities( SettleGeoTaxonomy::TYPE_COUNTRY, null, $wgLanguageCode );
		foreach ($countries as $country) {
			$data['countriesHtml'] .= '<option data-geo-id="'.$country['geonamesCode'].'" value="'.$country['name'].'">'.$country['name'].'</option>';
		}

	    $categories = SettleGeoCategories::getAllCategories();
	    foreach ($categories as $category) {
		    $data['categoriesHtml'] .= SettleGeoCategoryInput::displayCategoryRecursiveInput( $category, '', '', true );
	    }

	    $html = $this->templateParser->processTemplate('main', $data);
	    $this->getOutput()->addHTML($html);
    }

}