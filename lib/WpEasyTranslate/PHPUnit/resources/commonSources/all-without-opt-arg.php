<?php
/**
 * A set of WordPress translation functions.
 *
 * @see wp-includes/l10n.php
 */

translate('translate');

translate_with_gettext_context('translate_with_gettext_context', 'translate_with_gettext_context-context');

__('__');

esc_attr__('esc_attr__');

esc_html__('esc_html__');

_e('_e');

esc_attr_e('esc_attr_e');

esc_html_e('esc_html_e');

_x('_x', '_x-context');

_ex('_ex', '_ex-context');

esc_attr_x('esc_attr_x', 'esc_attr_x-context');

esc_html_x('esc_html_x', 'esc_html_x-context');

_n('_n-single', '_n-plural', 2);

_nx('_nx-single', '_nx-plural', 2, '_nx-context');

_n_noop('_n_noop-singular', '_n_noop-plural');

_nx_noop('_nx_noop-singular', '_nx_noop-plural', '_nx_noop-context');
