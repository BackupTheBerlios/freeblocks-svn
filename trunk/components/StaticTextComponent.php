
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class StaticTextComponent extends Component
{
	public function renderComponent()
	{
		$this->xtpl->assign('CONTENT', $this->getAttribute('text'));
		return parent::renderComponent();
	}
}

?>