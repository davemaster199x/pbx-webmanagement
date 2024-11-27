<?php

/*
 * Form Display
 * This function generates HTML form input fields based on an array and
 * a template.
 */

	function form_display( $form, $formats ) {
		if ( is_array( $form )) {
			$html = '';

			foreach ( $form as $element ) {
				if ( !isset( $element['type'] )) {
					continue;
				}

				if ( $element['type'] == 'parent-container' ) {
					$replace = $formats['parent-container'];
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );
					$replace = str_replace( '%CONTENT%', isset( $element['content'] ) ? $element['content'] : '', $replace );

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$replace = str_replace( '%CHILDREN%', isset( $element['children'] ) ? form_display( $element['children'], $formats ) : '', $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'autosuggest' ) {
					$replace = $formats['autosuggest'];
					$replace = str_replace( '%LABEL%',       isset( $element['label'] )       ? $element['label']       : '', $replace );
					$replace = str_replace( '%NAME%',        isset( $element['name'] )        ? $element['name']        : '', $replace );
					$replace = str_replace( '%VALUE%',       isset( $element['value'] )       ? $element['value']       : '', $replace );
					$replace = str_replace( '%PLACEHOLDER%', isset( $element['placeholder'] ) ? $element['placeholder'] : '', $replace );
					$replace = str_replace( '%PARAMS%',      isset( $element['params'] )      ? $element['params']      : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					if ( isset( $element['class'] )) {
						$replace = str_replace( '%CLASS%', ' ' . $element['class'], $replace );
					} else {
						$replace = str_replace( '%CLASS%', '', $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'button' ) {
					$replace = $formats['button'];
					$replace = str_replace( '%NAME%',    isset( $element['name'] )    ? $element['name']    : '', $replace );
					$replace = str_replace( '%VALUE%',   isset( $element['value'] )   ? $element['value']   : '', $replace );
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );
					$replace = str_replace( '%TOOLTIP%', isset( $element['tooltip'] ) ? $element['tooltip'] : '', $replace );

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'checkbox' ) {
					$replace = $formats['checkbox'];
					$replace = str_replace( '%LABEL%',   isset( $element['label'] )   ? $element['label']   : '', $replace );
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );

					preg_match( '/%%OPTIONS%%(.*)%%OPTIONS%%/s', $replace, $matches );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					if ( is_array( $element['options'] )) {
						$options = array();

						foreach ( $element['options'] as $i => $option ) {
							if ( is_array( $option )) {
								$options[ $i ] = $matches[1];
								$options[ $i ] = str_replace( '%DISPLAY%', isset( $option['display'] )  ? $option['display']  : '', $options[ $i ] );
								$options[ $i ] = str_replace( '%NAME%',    isset( $option['name'] )     ? $option['name']     : '', $options[ $i ] );
								$options[ $i ] = str_replace( '%VALUE%',   isset( $option['value'] )    ? $option['value']    : '', $options[ $i ] );
								$options[ $i ] = str_replace( '%CHECKED%', !empty( $option['checked'] ) ? ' checked'          : '', $options[ $i ] );
							}
						}

						$replace = str_replace( $matches[0], implode( '', $options ), $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'container' ) {
					$replace = $formats['container'];
					$replace = str_replace( '%ID%',      isset( $element['id'] )      ? $element['id']      : '', $replace );
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'hidden' ) {
					$replace = $formats['hidden'];
					$replace = str_replace( '%NAME%',    isset( $element['name'] )  ? $element['name']  : '', $replace );
					$replace = str_replace( '%VALUE%',   isset( $element['value'] ) ? $element['value'] : '', $replace );

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'hr' ) {
					$replace = $formats['hr'];

					$html .= $replace;
				} elseif ( $element['type'] == 'linebreak' ) {
					$replace = $formats['linebreak'];

					$html .= $replace;
				} elseif ( $element['type'] == 'multicheck' ) {
					$replace = $formats['multicheck'];
					$replace = str_replace( '%LABEL%',   isset( $element['label'] )   ? $element['label']   : '', $replace );
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					preg_match( '/%%OPTIONS%%(.*)%%OPTIONS%%/s', $replace, $matches );

					if ( is_array( $element['options'] )) {
						$options = array();

						foreach ( $element['options'] as $i => $option ) {
							$options[ $i ] = $matches[1];
							$options[ $i ] = str_replace( '%DISPLAY%', isset( $option['display'] ) ? $option['display'] : '', $options[ $i ] );
							$options[ $i ] = str_replace( '%NAME%',    "{$element['name']}[{$option['value']}]",              $options[ $i ] );
							$options[ $i ] = str_replace( '%VALUE%',   '1',                                                   $options[ $i ] );

							if ( $option['selected'] ) {
								$options[ $i ] = str_replace( '%CHECKED%', ' checked', $options[ $i ] );
							} else {
								$options[ $i ] = str_replace( '%CHECKED%', '', $options[ $i ] );
							}
						}

						$replace = str_replace( $matches[0], implode( '', $options ), $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'password' ) {
					$replace = $formats['password'];
					$replace = str_replace( '%LABEL%',       isset( $element['label'] )       ? $element['label']       : '', $replace );
					$replace = str_replace( '%NAME%',        isset( $element['name'] )        ? $element['name']        : '', $replace );
					$replace = str_replace( '%VALUE%',       isset( $element['value'] )       ? $element['value']       : '', $replace );
					$replace = str_replace( '%PLACEHOLDER%', isset( $element['placeholder'] ) ? $element['placeholder'] : '', $replace );
					$replace = str_replace( '%CLASS%',       isset( $element['class'] )       ? $element['class']       : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'radio' ) {
					$replace = $formats['radio'];
					$replace = str_replace( '%LABEL%',   isset( $element['label'] )   ? $element['label']   : '', $replace );
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );
					$replace = str_replace( '%NAME%',    isset( $element['name'] )    ? $element['name']    : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					preg_match( '/%%OPTIONS%%(.*)%%OPTIONS%%/s', $replace, $matches );

					if ( is_array( $element['options'] )) {
						$options = array();

						foreach ( $element['options'] as $i => $option ) {
							$options[ $i ] = $matches[1];
							$options[ $i ] = str_replace( '%DISPLAY%', isset( $option['display'] )  ? $option['display']  : '', $options[ $i ] );
							$options[ $i ] = str_replace( '%VALUE%',   isset( $option['value'] )    ? $option['value']    : '', $options[ $i ] );

							if ( $element['selected'] == $option['value'] ) {
								$options[ $i ] = str_replace( '%CHECKED%', ' checked', $options[$i] );
							} else {
								$options[ $i ] = str_replace( '%CHECKED%', '', $options[$i] );
							}
						}

						$replace = str_replace( $matches[0], implode( '', $options ), $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'select' ) {
					$replace = $formats['select'];
					$replace = str_replace( '%LABEL%',   isset( $element['label'] )   ? $element['label']   : '', $replace );
					$replace = str_replace( '%NAME%',    isset( $element['name'] )    ? $element['name']    : '', $replace );
					$replace = str_replace( '%CLASS%',   isset( $element['class'] )   ? $element['class']   : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					preg_match( '/%%OPTIONS%%(.*)%%OPTIONS%%/s', $replace, $matches );

					if ( isset( $element['options'] ) && is_array( $element['options'] )) {
						$options = array();

						foreach ( $element['options'] as $i => $option ) {
							$options[ $i ] = $matches[1];
							$options[ $i ] = str_replace( '%DISPLAY%', isset( $option['display'] )  ? $option['display']  : '', $options[ $i ] );
							$options[ $i ] = str_replace( '%VALUE%',   isset( $option['value'] )    ? $option['value']    : '', $options[ $i ] );

							if ( isset( $element['selected'] ) && $element['selected'] == $option['value'] ) {
								$options[ $i ] = str_replace( '%SELECTED%', ' selected', $options[ $i ] );
							} else {
								$options[ $i ] = str_replace( '%SELECTED%', '', $options[ $i ] );
							}
						}

						$replace = str_replace( $matches[0], implode( '', $options ), $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif( $element['type'] == 'submit' ) {
					$replace = $formats['submit'];
					$replace = str_replace( '%NAME%',  isset( $element['name'] )  ? $element['name']  : '', $replace );
					$replace = str_replace( '%VALUE%', isset( $element['value'] ) ? $element['value'] : '', $replace );
					$replace = str_replace( '%CLASS%', isset( $element['class'] ) ? $element['class'] : '', $replace );

					$data_values = '';

					if( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'textarea' ) {
					$replace = $formats['textarea'];
					$replace = str_replace( '%LABEL%',       isset( $element['label'] )       ? $element['label']       : '', $replace );
					$replace = str_replace( '%NAME%',        isset( $element['name'] )        ? $element['name']        : '', $replace );
					$replace = str_replace( '%VALUE%',       isset( $element['value'] )       ? $element['value']       : '', $replace );
					$replace = str_replace( '%PLACEHOLDER%', isset( $element['placeholder'] ) ? $element['placeholder'] : '', $replace );
					$replace = str_replace( '%WRAP%',        isset( $element['wrap'] )        ? ' wrap'                 : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					if ( isset( $element['class'] )) {
						$replace = str_replace( '%CLASS%', ' ' . $element['class'], $replace );
					} else {
						$replace = str_replace( '%CLASS%', '', $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$attr_values = '';

					if ( isset( $element['attr'] ) && is_array( $element['attr'] )) {
						foreach ( $element['attr'] as $attr ) {
							$attr_values .= " {$attr['name']}=\"{$attr['value']}\"";
						}
					}

					$replace = str_replace( '%ATTR%', $attr_values, $replace );

					$html .= $replace;
				} elseif ( $element['type'] == 'datepicker' ) {
					$replace = $formats['datepicker'];
					$replace = str_replace( '%LABEL%',   isset( $element['label'] )   ? $element['label']   : '', $replace );
					$replace = str_replace( '%NAME%',    isset( $element['name'] )    ? $element['name']    : '', $replace );
					$replace = str_replace( '%VALUE%',   isset( $element['value'] )   ? $element['value']   : '', $replace );
					$replace = str_replace( '%PARAMS%',  isset( $element['params'] )  ? $element['params']  : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					if ( isset( $element['class'] )) {
						$replace = str_replace( '%CLASS%', ' ' . $element['class'], $replace );
					} else {
						$replace = str_replace( '%CLASS%', '', $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$html .= $replace;
				} else {
					$replace = $formats['text'];
					$replace = str_replace( '%LABEL%',       isset( $element['label'] )       ? $element['label']       : '', $replace );
					$replace = str_replace( '%NAME%',        isset( $element['name'] )        ? $element['name']        : '', $replace );
					$replace = str_replace( '%VALUE%',       isset( $element['value'] )       ? htmlspecialchars( $element['value'] ) : '', $replace );
					$replace = str_replace( '%PLACEHOLDER%', isset( $element['placeholder'] ) ? $element['placeholder'] : '', $replace );

					if ( isset( $element['tooltip'] )) {
						$tooltip = str_replace( '%TOOLTIP%', $element['tooltip']['value'], $element['tooltip']['html'] );
						$replace = str_replace( '%TOOLTIP%', $tooltip,                     $replace );
					} else {
						$replace = str_replace( '%TOOLTIP%', '', $replace );
					}

					if ( isset( $element['class'] )) {
						$replace = str_replace( '%CLASS%', ' ' . $element['class'], $replace );
					} else {
						$replace = str_replace( '%CLASS%', '', $replace );
					}

					$data_values = '';

					if ( isset( $element['data'] ) && is_array( $element['data'] )) {
						foreach ( $element['data'] as $data ) {
							$data_values .= " data-{$data['name']}=\"{$data['value']}\"";
						}
					}

					$replace = str_replace( '%DATA%', $data_values, $replace );

					$attr_values = '';

					if ( isset( $element['attr'] ) && is_array( $element['attr'] )) {
						foreach ( $element['attr'] as $attr ) {
							$attr_values .= " {$attr['name']}=\"{$attr['value']}\"";
						}
					}

					$replace = str_replace( '%ATTR%', $attr_values, $replace );

					$html .= $replace;
				}
			}

			return $html;
		} else {
			return FALSE;
		}
	}

?>
