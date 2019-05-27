<?php

namespace ttwms;

use Lite\Core\Config;
use Lite\Core\Router;
use Lite\Core\View;
use Lite\Crud\ListOrderInterface;
use Lite\DB\Model;
use Lite\Exception\Exception;
use function Lite\func\array_clear_empty;
use function Lite\func\array_trim;
use function Lite\func\array_unshift_assoc;
use function Lite\func\h;
use function Lite\func\ha;

/**
 * Class ViewBase
 * @package ttwms
 */
class ViewBase extends View{
	const CLASS_DRAFT = 'state-flag state-flag-draft';
	const CLASS_NORMAL = 'state-flag state-flag-normal';
	const CLASS_DONE = 'state-flag state-flag-done';
	const CLASS_WARN = 'state-flag state-flag-warn';
	const CLASS_DISABLED = 'state-flag state-flag-disabled';
	private static $CURRENT_ACTIVE_URI;
	
	/**
	 * add access
	 * @param string $uri
	 * @param array $param
	 * @return string
	 */
	public static function getUrl($uri = '', $param = array()){
		$url = parent::getUrl($uri, $param);
		return $url;
	}
	
	public static function prettyTime($time_str){
		if(!$time_str){
			return '-';
		}
		return '<span title="'.date('Y-m-d H:i:s', strtotime($time_str)).'">'.date('y/m/d H:i', strtotime($time_str));
	}
	
	/**
	 * @param string $file_name
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function getImgUrl($file_name){
		return parent::getImgUrl($file_name ?: Config::get('app/default_image'));
	}
	
	/**
	 * @param string $field
	 * @param \Lite\DB\Model $model_instance
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function displayField($field, Model $model_instance = null){
		if(!$model_instance->$field){
			return '-';
		}
		
		
		$value = $model_instance->$field;
		$define = $model_instance->getPropertiesDefine($field);
		if($define['rel'] == 'upload-image'){
			$value = $value ?: Config::get('app/default_image'); //todo merge params
			return Upload::getThumbHtml($value);
		} else if($define['rel'] == 'upload-file'){
			$value = $value ?: Config::get('app/default_image');
			return '<a href="'.Config::get('upload/url').$value.'" target="_blank" title="'.h($value).'">下载</a>';
		} else if($define['rel'] == 'keywords' || $define['data-component'] == 'tags'){
			$t = explode(',', $value);
			$t = array_clear_empty(array_trim($t));
			if($t){
				return '<ul class="tags"><li>'.join('</li><li>', $t).'</li></ul>';
			}
			return '';
		} else if($define['type'] == 'datetime'){
			if($define['display']){
				if(is_callable($define['display'])){
					$define['display'] = call_user_func($define['display'], $model_instance);
				}
				return $define['display'];
			}
			$val = $model_instance->$field;
			$tbl = $model_instance->getTableName();
			if(!$val){
				return '-';
			}
			$t = strtotime($val);
			
			$h = date('Y-m-d H:i', $t);
			
			$class = str_replace('_', '-', 'datetime-'.$tbl.'-'.$field);
			return '<span class="'.$class.'" style="white-space:nowrap" title="'.date('Y-m-d H:i', strtotime($val)).'">'.$h.'</span>';
		} else{
			$html = parent::displayField($field, $model_instance);
			if($define['options'] && $html){
				$tbl = $model_instance->getTableName();
				// $class = strtolower(str_replace('_', '-', 'field-state field-'.$tbl.'-'.$field.'-'.$value));
				// if(strpos(strtolower($field),"status") !== false){
				// 	$class .=" field-status";
				// }
				$class = strtolower(str_replace('_', '-', 'field-state'));
				if(strpos(strtolower($field),"status") !== false){
					$class .=" field-status";
				}
				$html = '<span class="'.$class.'">'.$html.'</span>';
			}
			return $html;
		}
	}
	
	/**
	 * display $model_instance
	 * @param $field
	 * @param Model $model_instance
	 * @param bool $confirm
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function displayFieldQuickUpdate($field, Model $model_instance, $confirm = false){
		$define = $model_instance->getPropertiesDefine($field);
		if($define['options']){
			return self::displayOptionFieldQuickUpdate($field, $model_instance, $confirm);
		}
		return self::displayField($field, $model_instance);
	}
	
	
	/**
	 * @param ListOrderInterface|Model $model_instance
	 * @param $field
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function displayListOrderUpdate($model_instance, $field){
		$val = $model_instance->$field;
		
		$pk = $model_instance->getPrimaryKey();
		$inc_url = self::getUrl(self::getControllerAbbr().'/increaseField', array(
			'pk_val' => $model_instance->$pk,
			'field'  => $field,
			'offset' => 1
		));
		$des_url = self::getUrl(self::getControllerAbbr().'/increaseField', array(
			'pk_val' => $model_instance->$pk,
			'field'  => $field,
			'offset' => -1
		));
		
		$html = '<a href="'.$inc_url.'" data-component="async" class="priority-change priority-increase"></a>';
		$html .= '<span class="priority-label">'.$val.'</span>';
		$html .= '<a href="'.$des_url.'" data-component="async" class="priority-change priority-decrease"></a>';
		return $html;
	}
	
	
	/**
	 * 快速渲染
	 * @param Model $model_instance
	 * @param $field
	 * @param $element_name
	 * @return string
	 */
	public static function renderElementQuick(Model $model_instance, $field,$element_name=null){
		$define = $model_instance->getPropertiesDefine($field);
		$element_name = $element_name?:$field;
		return parent::renderFormElement($model_instance->$field, $element_name, $define, $model_instance);
	}
	
	/**
	 * 显示快速更新字段
	 * @param $field
	 * @param Model $model_instance
	 * @param bool $confirm
	 * @return string
	 * @throws Exception
	 */
	public static function displayOptionFieldQuickUpdate($field, Model $model_instance, $confirm = false){
		$pk = $model_instance->getPrimaryKey();
		$define = $model_instance->getPropertiesDefine($field);
		$html = '<dl class="drop-list drop-list-left">'.
			'<dt><span>'.self::displayField($field, $model_instance).'</span></dt><dd>';
		if(is_callable($define['options'])){
			$define['options'] = call_user_func($define['options'], $model_instance);
		}
		foreach($define['options'] as $k => $n){
			if($k != $model_instance->{$field}){
				if(!$confirm){
					$com = 'data-component="async"';
				} else{
					$com = 'data-component="confirm,async" data-confirm-message="确定进行该项操作？"';
				}
				$html .= '<a href="'.self::getUrl(self::getControllerAbbr().'/updateField', array($pk    => $model_instance->$pk,
				                                                                                  $field => $k
					)).'" '.$com.'>'.h($n).'</a>';
			}
		}
		$html .= '</dd></dl>';
		return $html;
	}
	
	
	/**
	 * 渲染搜索表单元素
	 * @param $value
	 * @param $field
	 * @param $define
	 * @param null $model_instance
	 * @param array $extend_attr
	 * @return mixed|string
	 * @throws \Lite\Exception\Exception
	 */
	public static function renderSearchFormElement($value, $field, $define, $model_instance = null, $extend_attr = array()){
		unset($define['rel']);
		unset($define['default']);
		unset($define['required']);
		
		$extend_attr = array_merge(array(
			'title' => $define['alias'],
		), $extend_attr);
		
		if(in_array($define['type'], ['date', 'datetime', 'time', 'timestamp'])){
			return static::renderDateRangeElement($value, $field, $define, $model_instance, $extend_attr);
		}
		if(in_array($define['type'], array('text', 'simple_rich_text', 'rich_text'))){
			$define['type'] = 'string';
		}
		if($define['options']){
			$define['options'] = is_callable($define['options']) ? call_user_func($define['options']) : $define['options'];
			array_unshift_assoc($define['options'], '', ' -- '.$define['alias'].' -- ');
		}
		$extend_attr = array_merge(array('placeholder' => $define['alias']), $extend_attr);
		return static::renderFormElement($value, $field, $define, $model_instance, $extend_attr, false);
	}
	
	/**
	 * 显示集合
	 * @param $sets
	 * @param $options
	 * @param \Lite\DB\Model $model_instance
	 * @return string
	 */
	public static function displaySet($sets, $options, Model $model_instance){
		$vs = explode(',', $sets);
		$t = array();
		foreach($vs as $v){
			$t[] = $options[$v];
		}
		return join('，', $t);
	}
	
	
	public static function renderSearchDateRange($values, $field, $title = '', $max_day = null){
		$values[0] = $values[0] ? date('Y-m-d', strtotime($values[0])) : '';
		$values[1] = $values[1] ? date('Y-m-d', strtotime($values[1])) : '';
		$max_day = isset($max_day) ? $max_day : date('Y-m-d');
		$html = '<span class="search-date-range">';
		$html .= $title ? '<label for="'.$field.'_fst">'.$title.'</label>' : '';
		$html .= '<input type="text" autocomplete="off" data-component="timepicker" data-timepicker-max="'.$max_day.'" data-timepicker-format="date" id='.$field.'_fst" name="'.$field.'[]" value="'.ha($values[0]).'" placeholder="开始时间">';
		$html .= ' - ';
		$html .= '<input type="text" autocomplete="off" data-component="timepicker" data-timepicker-max="'.$max_day.'"  data-timepicker-format="date" name="'.$field.'[]" value="'.ha($values[1]).'" placeholder="结束时间">';
		$html .= '</span>';
		return $html;
	}
	
	/**
	 * @param $value_range
	 * @param $field
	 * @param array $define
	 * @param null $model_instance
	 * @param array $extend_attr
	 * @return string
	 */
	public static function renderDateRangeElement($value_range, $field, $define=[], $model_instance = null, $extend_attr = array()){
		return '<span class="date-range-input">'.
			parent::renderDateRangeElement($value_range, $field, $define, $model_instance, $extend_attr).
			'</span>';
	}
	
	public static function renderDateRangeSelection($title, $name, $values = []){
		$html = '<div class="date-range-selection">';
		$html .= "<label>$title</label>";
		$html .= '<input type="text" data-component="timepicker" data-timepicker-format="date" name="'.$name.'[]" value="'.ha($values[0]).'">';
		$html .= ' - ';
		$html .= '<input type="text" data-component="timepicker" data-timepicker-format="date" name="'.$name.'[]" value="'.ha($values[1]).'">';
		$html .= '</div>';
		return $html;
		
	}
	
	/**
	 * 绑定上传图片
	 * @param $value
	 * @param string $field
	 * @param array $define
	 * @param null $model_instance
	 * @param array $extend_attr
	 * @param bool $add_default_selection
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function renderFormElement($value, $field, $define, $model_instance = null, $extend_attr = array(), $add_default_selection = true){
		if($model_instance && !CurrentUser::checkFieldAccess(get_class($model_instance), $field)){
			return '<span class="no-field-update-access"></span>';
		}
		if($define['rel'] == 'upload-image'){
			$is_vip = true;
			return $is_vip?static::buildElement('input', array(
				'type'                        => 'hidden',
				'name'                        => $field,
				'value'                       => $model_instance->$field,
				'data-component'              => 'vipuploader',
				'data-src'                    => Upload::getImageUrl($model_instance->$field),
				'data-thumb'                  => Upload::getThumbUrl($model_instance->$field),
				//'data-vipuploader-select_url' => self::getUrl('disk', [self::REQ_DES_KEY => self::REQ_IFRAME]),
			), $define):static::buildElement('input', array(
				'type'           => 'hidden',
				'name'           => $field,
				'value'          => $model_instance->$field,
				'data-component' => 'uploader',
				'data-src'       => Upload::getImageUrl($model_instance->$field),
				'data-thumb'     => Upload::getThumbUrl($model_instance->$field),
			), $define);
		}elseif($define['rel'] == 'upload-file'){
			return static::buildElement('input', array(
				'type'               => 'hidden',
				'name'               => $field,
				'value'              => $model_instance->$field,
				'data-uploader-type' => 'file',
				'data-component'     => 'fileuploader',
				'data-src'           => Config::get('upload/url').$model_instance->$field,
			), $define);
		} elseif($define['type'] == 'set' || $define['rel'] == 'chosen'){
			$def = array(
				'name'             => $field.'[]',
				'data-component'   => 'chosen',
				'data-placeholder' => '请选择',
				'multiple'         => 'multiple',
			);
			$extend_attr = is_array($define['extend_attr']) ? array_merge($def, $define['extend_attr']) : $def;
			return static::buildSelectElement($value, $extend_attr, $define, $model_instance);
		} elseif($define['type'] == 'select'){
			$def = array(
				'name' => $field,
			);
			$extend_attr = is_array($define['extend_attr']) ? array_merge($def, $define['extend_attr']) : $def;
			return static::buildSelectElement($value, $extend_attr, $define, $model_instance, $add_default_selection);
		} else if(isset($define['placeholder'])){
			return static::buildElement('input', array(
				'type'        => 'text',
				'maxlength'   => $define['length'] ?: null,
				'name'        => $field,
				'value'       => $value,
				'placeholder' => $define['placeholder']
			), $define);
		} else{
			return parent::renderFormElement($value, $field, $define, $model_instance, $extend_attr, $add_default_selection);
		}
	}
	
	/**
	 * add element class
	 * @param $value
	 * @param array $attributes
	 * @param array $define
	 * @param $model_instance
	 * @param bool $add_default_selection
	 * @return string
	 */
	public static function buildSelectElement($value, $attributes = array(), $define = array(), $model_instance, $add_default_selection = true){
		$required = $define['required'];
		//transform closure options to array
		if(is_callable($define['options'])){
			$define['options'] = call_user_func($define['options'], $model_instance);
		}
		
		if($add_default_selection){
			$attributes['placeholder'] = '';
		}
		$extend_attr_str = static::buildAttributesStr($attributes);
		$html = '<select '
			.($required ? ' required="required"' : '')
			.$extend_attr_str.'>';
		$options = $define['options'];
		$disabled = false;
		if($add_default_selection){
			$html .= static::buildElement('option', array(
				'value' => '',
				'text'  => '请选择'
			), $define);
		}
		foreach($options ?: [] as $k => $n){
			if(is_array($n)){
				$k = $n['value'];
				$disabled = $n['disabled'];
				$n = $n['name'];
			}
			$attr = array('value' => $k, 'text' => $n);
			if($disabled){
				$attr['disabled'] = 'disabled';
			}
			if(in_array((string)$k, explode(',', $value)) || (!strlen($value) && isset($define['default']) && $define['default'] == $k)){
				$attr['selected'] = 'selected';
			}
			$html .= static::buildElement('option', $attr, $define);
		}
		
		$html .= '</select>';
		return $html;
	}
	
	/**
	 * add element class
	 * @param $tag
	 * @param array $attributes
	 * @param array $define
	 * @return string
	 */
	public static function buildElement($tag, $attributes = array(), $define = array()){
		if($define['rel'] == 'keywords' || $define['rel'] == 'tags'){
			return self::buildTagsInput($tag, $attributes, $define);
		}
		
		$tag = strtolower($tag);
		$class = $attributes['class'];
		switch($tag){
			case 'input':
				if($define['type'] == 'date'){
					$attributes = array_merge($attributes, [
						'type'                   => 'text',
						'data-component'         => 'timepicker',
						'data-timepicker-format' => 'date'
					]);
				}
				if($define['type'] == 'datetime' || $define['type'] == 'timestamp'){
					$attributes = array_merge($attributes, [
						'type'                   => 'text',
						'data-component'         => 'timepicker',
						'data-timepicker-format' => 'datetime'
					]);
				}
				if($define['type'] == 'time'){
					$attributes = array_merge($attributes, [
						'type'                   => 'text',
						'data-component'         => 'timepicker',
						'data-timepicker-format' => 'time'
					]);
				}
				break;
			
			case 'textarea':
				if($define['type'] == 'simple_rich_text'){
					$attributes['data-component'] = 'richeditor';
					$attributes['data-richeditor-mode'] = 'lite';
					$class .= ' medium-txt';
				} else if($define['type'] == 'rich_text'){
					$attributes['data-component'] = 'richeditor';
					$attributes['data-richeditor-mode'] = 'normal';
					$class .= ' large-txt';
				} else{
					$class .= ' small-txt';
				}
				break;
		}
		
		if($class){
			$attributes['class'] = $class;
		}
		return parent::buildElement($tag, $attributes, $define);
	}
	
	private static function buildTagsInput($tag, $attributes = array(), $define = array()){
		$values = array_clear_empty(array_trim(explode(',', $attributes['text'])));
		$name = $attributes['name'];
		
		$html = '';
		$html .= '<div class="tags-input">';
		$html .= '<input type="hidden" name="'.$name.'" value="'.h($attributes['text'] ?: $attributes['value']).'"/>';
		if($values){
			$del = '<span class="del-tag" title="删除">x</span><span>';
			$html .= '<ul class="tags"><li>'.$del.join('</span></li><li>'.$del, $values).'</span></li></ul>';
		} else{
			$html .= '<ul class="tags"></ul>';
		}
		$html .= '<input type="text" value="" placeholder="回车输入" class="txt"/>';
		$html .= '</div>';
		return $html;
	}
	
	/**
	 * @param $src
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function buildUploadImage($src){
		return Config::get('upload/url').$src;
	}
	
	private static $page_path = [];
	
	/**
	 * @param $array
	 */
	public static function setPagePath($array){
		self::$page_path = $array;
	}
	
	public static function setCurrentActiveUri($uri){
		self::$CURRENT_ACTIVE_URI = $uri;
	}
	
	public static  function getCurrentActiveUri(){
		return self::$CURRENT_ACTIVE_URI?:parent::getCurrentUri();
	}
	
	/**
	 * get page title from page path
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function getPageTitle(){
		$site_name = Config::get('app/site_name');
		$tmp = [$site_name];
		foreach(self::$page_path as $k => $v){
			if(is_numeric($k)){
				$tmp[] = $v;
			} else{
				$tmp[] = h($k);
			}
		}
		return join(' - ', array_reverse($tmp));
	}
	
	/**
	 * get page bread crumbs from page path
	 * @return string
	 */
	public static function getPageBreadCrumbs(){
		return self::$page_path ? self::buildBreadCrumbs(self::$page_path) : '';
	}
	
	/**
	 * 设置面包屑
	 * @param array ...$array
	 * @return string
	 */
	public static function buildBreadCrumbs($array){
		$html = '<ul class="breadcrumbs">';
		$html .= '<li><a href="'.self::getUrl().'">首页</a></li>';
		foreach($array as $k => $v){
			if(is_numeric($k)){
				$html .= '<li><span>'.$v.'</span></li>';
			} else{
				$html .= '<li><a href="'.self::getUrl($v).'">'.h($k).'</a></li>';
			}
		}
		$html .= '</ul>';
		return $html;
	}
	
	public static function getCurrencyPriceHtml($price, $currency_code){
		return '<span data-component="sale/exchange" data-currency-code="'.$currency_code.'" data-price="'.$price.'">'.$currency_code.floatval($price).'</span>';
	}
	
	/**
	 * 获取table log查看链接
	 * @param \Lite\DB\Model $obj
	 * @return string
	 * @throws \Lite\Exception\Exception
	 */
	public static function getTableLogLink(Model $obj){
		$pkv = $obj->getValue($obj->getPrimaryKey());
		$url = static::getUrl('sys/SysTableLog/info', array('tbl_name' => $obj->getTableFullName(), 'ref_id' => $pkv));
		$html = '<a href="'.$url.'" data-component="popup">日志</a>';
		return $html;
	}
	
	
	
	public static function showCountryFlag($country_code){
		static $load_css;
		
		$html = '';
		if(!$load_css){
			$load_css = true;
			$html .= self::getCss('flags.min.css');
		}
		$html .= '<img src="'.self::getImgUrl('blank.gif').'" class="flag flag-'.strtolower($country_code).'">';
		echo $html;
	}
	
	/**
	 * 关闭窗口按钮
	 * @return string
	 */
	public static function getDialogCloseBtn(){
		if(Router::get('ref') == 'iframe'){
			return '<button class="btn btn-weak close-current-win-btn">关闭</button>';
		}
		return '';
	}
	
	/**
	 * 关闭窗口按钮并同时刷新父页面
	 * @return string
	 */
	public static function getReloadDialogCloseBtn(){
		if(Router::get('ref') == 'iframe'){
			$html = <<<EOL
<span class="btn btn-weak close-current-win-btn" id="Reload-Dialog-Close-Btn">关闭</span>
<script>
	seajs.use(["jquery","ywj/popup"],function($,Pop){
		var close = function(){
			parent.window.location.reload();
			Pop.closeCurrentPopup();
		};
		$("#Reload-Dialog-Close-Btn").click(close);
	})
</script>
EOL;
			return $html;
		}
		return '';
	}
	
	/**
	 * 列表导出按钮
	 * @param array $list 列表数据
	 * @param string $url 请求导出的url
	 * @return string
	 */
	public static function getExportBtn($list,$url){
		if(!$list){
			return "<span class='btn btn-disabled'>导出</span>";
		}
		return "<input type='submit' formaction='{$url}' value='导出'>";
	}
	
}