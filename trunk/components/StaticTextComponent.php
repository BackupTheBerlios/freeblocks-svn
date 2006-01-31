
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class StaticTextComponent extends Component
{
	public function __construct()
	{
		parent::__construct();
		$this->addPropertyBefore('width', 'text', 'Text', BaseComponent::TYPE_TEXT, '');
	}

	public function renderComponent()
	{
		$this->xtpl->assign('CONTENT', $this->getPropertyValue('text'));
		return parent::renderComponent();
	}
}

?>