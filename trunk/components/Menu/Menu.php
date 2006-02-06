
<?php

require_once( dirname(__FILE__) . "/../../base/Component.php");


class Menu extends Component
{

	public function __construct()
	{
		parent::__construct();

		$this->addPropertyArray('item', array(
			'label' => new Property('label', 'Label', BaseComponent::TYPE_TEXT, ''),
			'url' => new Property('url', 'Target', BaseComponent::TYPE_TEXT, '' )
		));
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

				$this->xtpl->assign('LABEL', $label);
				$this->xtpl->assign('URL', $url);

				$this->xtpl->parse('main.item');
			}
		}

		$this->xtpl->assign('CONTENT', $content);

		return parent::renderComponent();
	}
}

?>