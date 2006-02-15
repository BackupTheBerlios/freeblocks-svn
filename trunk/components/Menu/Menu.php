
<?php

require_once( dirname(__FILE__) . "/../../base/Component.php");


class Menu extends Component
{

	public function __construct()
	{
		parent::__construct();

		$this->addPropertyArray('item', array( 'label', 'page', 'url') );

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

		if( $this->hasProperty('_sub') )
		{
			foreach($this->getPropertyValue('_sub') as $post)
			{
				$label= isset($post['label'])?$post['label']:'no label';
				$url= isset($post['url'])?$post['url']:'';
				$page= isset($post['page'])?$post['page']:'';

				$this->xtpl->assign('LABEL', $label);

				if( $page == 'true' )
				{
					$this->xtpl->assign('URL', '?page=' . $url);
				}
				else
				{
					$this->xtpl->assign('URL', $url);
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