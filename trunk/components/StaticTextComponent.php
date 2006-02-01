
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class StaticTextComponent extends Component
{
	public function __construct()
	{
		parent::__construct();
		$this->addPropertyBefore('width', 'text', 'Text', BaseComponent::TYPE_TEXT, '');
	}

	public function getIcon()
	{
		return 'indicator.gif';
	}

	public function renderComponent()
	{
		$text= ($this->hasProperty('text') && ($this->getPropertyValue('text') != ''))?$this->getPropertyValue('text'):'no text';
		$this->xtpl->assign('CONTENT', $text);
		return parent::renderComponent();
	}
}

?>