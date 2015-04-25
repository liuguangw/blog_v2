<?php

namespace liuguang\blog\view;

/**
 *
 * @author liuguang
 *        
 */
class Fenye {
	/**
	 * 获取分页代码
	 *
	 * @param string $urlTpl
	 *        	url模板
	 * @param int $page
	 *        	当前页码
	 * @param int $pageNum 页码总数
	 * @return string
	 */
	public static function getNav($urlTpl, $page, $pageNum) {
		$start_page = $page - 6;
		$end_page = $page + 6;
		// 分页
		$html = '<nav id="f_fenye">
  		<ul class="pagination">';
		if ($page == 1)
			$html .= ('<li class="disabled">
                    <a href="' . str_replace ( '--page--', 1,$urlTpl ) . '" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    </a></li>');
		else
			$html .= ('<li>
                    <a href="' . str_replace ( '--page--', ($page - 1),$urlTpl  ) . '" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    </a></li>');
		for($i = $start_page; $i <= $end_page; $i ++) {
			if (($i < 1) || ($i > $pageNum))
				continue;
			$url = str_replace ( '--page--', $i,$urlTpl  );
			if ($i == $page)
				$html .= ('<li class="active">
                    <a href="' . $url . '">' . $i . '
                    <span class="sr-only">(current)</span>
                    </a></li>');
			else
				$html .= ('<li>
                    <a href="' . $url . '">' . $i . '
                    <span class="sr-only">(current)</span>
                    </a></li>');
		}
		if ($page == $pageNum)
			$html .= ('<li class="disabled">
                    <a href="' . str_replace ( '--page--', $page,$urlTpl  ) . '" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    </a></li>');
		else
			$html .= ('<li>
                    <a href="' . str_replace ( '--page--',($page + 1),$urlTpl  ) . '" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    </a></li>');
		$html.='</ul></nav>';
		return $html;
	}
}