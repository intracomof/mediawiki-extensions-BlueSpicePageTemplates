<?php

 use BlueSpice\Tests\BSApiTasksTestBase;

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BlueSpicePageTemplates
 */
class BSApiPageTemplatesTasksTest extends BSApiTasksTestBase {

	protected $tablesUsed = [ 'bs_pagetemplate' ];

	protected function skipAssertTotal() {
		return true;
	}

	public function addDBData() {
		 // addDBDataOnce fails with usage of @dataProvider...
		$oPageTemplateFixtures = new BSPageTemplateFixtures();
		foreach ( $oPageTemplateFixtures->makeDataSets() as $dataSet ) {
			$this->db->insert( 'bs_pagetemplate', $dataSet );
		}
		return;
	}

	protected function getModuleName() {
		return 'bs-pagetemplates-tasks';
	}

	public function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	public function testDoEditTemplate() {
		// add template
		$oData = $this->executeTask(
			'doEditTemplate',
			[
				'desc' => 'Dummy template',
				'label' => 'Dummy 1',
				'template' => 'Dummy 1 title',
				'targetns' => NS_FILE
			]
		);

		$this->assertTrue( $oData->success );

		$this->assertSelect(
			'bs_pagetemplate',
			[ 'pt_id', 'pt_template_title', 'pt_target_namespace' ],
			[ "pt_label = 'Dummy 1'" ],
			[ [ 9, 'Dummy 1 title', 6 ] ]
		);

		$iIDAdded = 9;

		// edit template
		$oData = $this->executeTask(
			'doEditTemplate',
			[
				'id' => $iIDAdded,
				'desc' => 'Faux template',
				'label' => 'Faux 1',
				'template' => 'Faux 1 title',
				'targetns' => NS_MAIN
			]
		);

		$this->assertTrue( $oData->success );

		$this->assertSelect(
			'bs_pagetemplate',
			[ 'pt_template_title', 'pt_target_namespace' ],
			[ 'pt_id = 9' ],
			[ [ 'Faux 1 title', 0 ] ]
		);
	}

	public function testDoDeleteTemplates() {
		$aIDsToDelete = [
			1 => 'Test_01',
			8 => 'Test_08'
		];

		foreach ( $aIDsToDelete as $iID => $sTitle ) {
			$this->assertFalse( $this->isDeleted( $iID ) );
		}

		$oData = $this->executeTask(
			'doDeleteTemplates',
			[
				'ids' => $aIDsToDelete
			]
		);

		$this->assertTrue( $oData->success );

		foreach ( $aIDsToDelete as $iID => $sTitle ) {
			$this->assertTrue( $this->isDeleted( $iID ) );
		}
	}

	protected function isDeleted( $iID ) {
		$db = $this->db;
		$res = $db->select( 'bs_pagetemplate', [ 'pt_id' ],
			[ 'pt_id = ' . $iID ], wfGetCaller() );
		if ( $res->numRows() === 0 ) {
			return true;
		}

		return false;
	}
}
