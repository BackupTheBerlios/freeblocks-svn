
<?php

require_once( dirname(__FILE__) . "/../base/Component.php");


class Board extends Component
{

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
				$title= isset($post['title'])?$post['title']:'no title';
				$user= isset($post['user'])?$post['user']:'anonymous';
				$msg= isset($post['msg'])?$post['msg']:'no message';

				$content.= "
				<div class='post'>
					<div class='title'>{$title}</div>
					<div class='user'>Posted by {$user}</div>
					<div class='text'>{$msg}</div>
				</div>";
			}
		}

		$this->xtpl->assign('CONTENT', $content);

		return parent::renderComponent();
	}
}

?>