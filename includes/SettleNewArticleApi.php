<?php

class SettleNewArticleApi extends ApiBase {

	const STATUS_NOTHING_FOUND = 0;
	const STATUS_EXACT_MATCH = 1;
	const STATUS_FUZZY_MATCH = 2;

	public function execute() {

		$result = array(
			'status' => self::STATUS_NOTHING_FOUND,
			'pages' => array(),
			'message' => $result['message'] = wfMessage('settlenewarticle-message-api-nothing-found')->plain()
		);

		$params = $this->extractRequestParams();

		$query = SphinxStore::getInstance()->getQuery();

		$sql = "SELECT *, IN( properties.geocodes, {$params['geo_ids']} )
  				AND IN( properties.geocategoryid, {$params['category_id']} )
                AS p
				FROM wiki_rt WHERE alias_title = '{$params['title_value']}' AND p = 1;";

		$sphinxResult = $query->query( $sql )->execute();

		if( $sphinxResult->count() ) {
			$result['status'] = self::STATUS_EXACT_MATCH;
			$result['message'] = wfMessage('settlenewarticle-message-api-exact-match')->plain();

			foreach ($sphinxResult as $r) {
				$rTitle = Title::newFromText( $r['page_title'] );
				$properties = json_decode( $r['properties'], true );
				$result['pages'][] = array(
					'page_title' => $rTitle->getBaseText(),
					'alias_title' => $r['alias_title'],
					'url' => $rTitle->getFullURL(),
					'description' => ($properties['short_description'] ? $properties['short_description'][0] : '—'),
					'location' => SettleGeoSearch::formatLocationBreadcrumbs( $properties ),
					'category_name' => $properties['geocategory']
				);
			}

		}else{


			$sql = "SELECT *, IN( properties.geocodes, {$params['geo_ids']} )
  				AND IN( properties.geocategoryid, {$params['category_id']} )
  				AS p
				FROM wiki_rt WHERE MATCH('\"{$params['title_value']}\"/1') AND p = 1;";
			$sphinxResult = $query->query( $sql )->execute();

			if( $sphinxResult->count() ) {
				$result['status'] = self::STATUS_FUZZY_MATCH;
				$result['message'] = wfMessage('settlenewarticle-message-api-fuzzy-match')->plain();

				foreach ($sphinxResult as $r) {
					$rTitle = Title::newFromText( $r['page_title'] );
					$properties = json_decode( $r['properties'], true );
					$result['pages'][] = array(
						'page_title' => $rTitle->getBaseText(),
						'alias_title' => $r['alias_title'],
						'url' => $rTitle->getFullURL(),
						'description' => ($properties['short_description'] ? $properties['short_description'][0] : '—'),
						'location' => SettleGeoSearch::formatLocationBreadcrumbs( $properties ),
						'category_name' => $properties['geocategory']
					);
				}

			}

		}

		$this->getResult()->addValue( null, 'settlenewarticle', $result );

	}

	protected function getAllowedParams( /* $flags = 0 */ ) {
		return array(
			'title_value' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string'
			),
			'geo_ids' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string'
			),
			'category_id' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'integer'
			)
		);
	}


}