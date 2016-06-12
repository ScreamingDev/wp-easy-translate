<?php
/**
 * A set of WordPress translation functions.
 *
 * @see wp-includes/l10n.php
 */

translate('translate', 'test');

translate_with_gettext_context('translate_with_gettext_context', 'translate_with_gettext_context-context', 'test');

__('__', 'test');

esc_attr__('esc_attr__', 'test');

esc_html__('esc_html__', 'test');

_e('_e', 'test');

esc_attr_e('esc_attr_e', 'test');

esc_html_e('esc_html_e', 'test');

_x('_x', '_x-context', 'test');

_ex('_ex', '_ex-context', 'test');

esc_attr_x('esc_attr_x', 'esc_attr_x-context', 'test');

esc_html_x('esc_html_x', 'esc_html_x-context', 'test');

_n('_n-single', '_n-plural', 2, 'test');

_nx('_nx-single', '_nx-plural', 2, '_nx-context', 'test');

_n_noop('_n_noop-singular', '_n_noop-plural', 'test');

_nx_noop('_nx_noop-singular', '_nx_noop-plural', '_nx_noop-context', 'test');
