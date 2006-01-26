
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class StaticTextComponent extends Component
{
	public function __construct()
	{
		$this->addProperty('text', 'Text', BaseComponent::TYPE_TEXT, '');

		parent::__construct();
	}

	public function renderComponent()
	{
		$this->xtpl->assign('CONTENT', $this->getPropertyValue('text'));
		return parent::renderComponent();
	}
}

?>