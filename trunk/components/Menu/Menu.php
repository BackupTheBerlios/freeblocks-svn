
<?php

require_once( dirname(__FILE__) . "/../../base/Component.php");


class Menu extends Component
{

	public function __construct()
	{
		parent::__construct();

		//$this->addPropertyArray('item', array( 'label', 'page', 'target') );

		// tell the base class we handle children nodes
		$this->_has_children_handler= true;
	}

	public function getIcon()
	{
		return 'board.png';
	}

	public function renderComponent()
	{
		$content= "";

		if( $this->hasDatasource() )
		{
			$ds= $this->getDatasource();

			foreach( $ds->content as $item )
			{
				$label= isset($item['label'])?$item['label']:'no label';
				$target= isset($item['target'])?$item['target']:'';
				$page= isset($item['page'])?$item['page']:'';

				$this->xtpl->assign('LABEL', $label);

				if( $page == 'true' )
				{
					$this->xtpl->assign('TARGET', '?page=' . $target);
				}
				else
				{
					$this->xtpl->assign('TARGET', $target);
				}

				$this->xtpl->parse('main.item');
			}
		}

		$this->xtpl->assign('CONTENT', $content);

		return parent::renderComponent();
	}

	/**
	 * This function return the html to include in the property panel
	 * to handle children nodes of this component
	 *
	 * @return text
	 */
	protected function _getPropertyPanelChildrenHandler()
	{
		$this->xtpl->assign('CSS_CLASS', 'item_model');
		$this->xtpl->parse('multi_item.multi_item_line');
		$this->xtpl->parse('multi_item.add_button');

		$this->xtpl->parse('multi_item');
		return $this->xtpl->text('multi_item');
	}
}

?>