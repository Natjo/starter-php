<?php

if (!function_exists('sg_table_normalize_cell')) {
    function sg_table_normalize_cell($cell, string $default_tag = 'td'): array
    {
        if (is_array($cell)) {
            $cell['type'] = ($cell['type'] ?? $default_tag) === 'th' ? 'th' : 'td';
            return $cell;
        }

        return [
            'type' => $default_tag === 'th' ? 'th' : 'td',
            'text' => (string) $cell,
        ];
    }
}

if (!function_exists('sg_table_row_col_count')) {
    /**
     * Nombre de colonnes logiques d'une ligne (colspan inclus).
     */
    function sg_table_row_col_count(array $row): int
    {
        $count = 0;

        foreach ($row as $cell) {
            if (is_array($cell) && !empty($cell['colspan'])) {
                $count += max(1, (int) $cell['colspan']);
            } else {
                $count += 1;
            }
        }

        return $count;
    }
}

if (!function_exists('sg_table_pad_row')) {
    /**
     * Complète une ligne avec des cellules vides pour atteindre le nombre de colonnes cible.
     */
    function sg_table_pad_row(array $row, int $target_cols): array
    {
        $count = sg_table_row_col_count($row);

        while ($count < $target_cols) {
            $row[] = [
                'type' => 'td',
                'text' => '',
            ];
            $count++;
        }

        return $row;
    }
}

if (!function_exists('sg_table_normalize_grid')) {
    /**
     * Normalise un tableau de lignes (chaque ligne = tableau de colonnes).
     * Aligne toutes les lignes sur le même nombre de colonnes.
     */
    function sg_table_normalize_grid(array $rows): array
    {
        $normalized = [];
        $max_cols = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $max_cols = max($max_cols, sg_table_row_col_count($row));
        }

        if ($max_cols < 1) {
            return [];
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $normalized[] = sg_table_pad_row($row, $max_cols);
        }

        return $normalized;
    }
}

if (!function_exists('sg_table_prepare_header_row')) {
    function sg_table_prepare_header_row(array $row): array
    {
        $cells = [];

        foreach ($row as $cell) {
            $normalized = sg_table_normalize_cell($cell, 'th');
            if ($normalized['type'] === 'th' && empty($normalized['scope'])) {
                $normalized['scope'] = 'col';
            }
            $cells[] = $normalized;
        }

        return $cells;
    }
}

if (!function_exists('sg_table_prepare_body_row')) {
    function sg_table_prepare_body_row(array $row): array
    {
        $cells = [];

        foreach ($row as $cell) {
            $default_tag = (is_array($cell) && ($cell['type'] ?? '') === 'th') ? 'th' : 'td';
            $normalized = sg_table_normalize_cell($cell, $default_tag);
            if ($normalized['type'] === 'th' && empty($normalized['scope'])) {
                $normalized['scope'] = 'row';
            }
            $cells[] = $normalized;
        }

        return $cells;
    }
}

if (!function_exists('sg_table_split_rows')) {
    /**
     * @return array{head: array, body: array}
     */
    function sg_table_split_rows(array $rows, bool $has_header = true): array
    {
        $grid = sg_table_normalize_grid($rows);

        if (empty($grid)) {
            return ['head' => [], 'body' => []];
        }

        $head = $has_header ? [sg_table_prepare_header_row($grid[0])] : [];
        $body = [];
        $body_rows = $has_header ? array_slice($grid, 1) : $grid;

        foreach ($body_rows as $row) {
            $body[] = sg_table_prepare_body_row($row);
        }

        return [
            'head' => $head,
            'body' => $body,
        ];
    }
}

if (!function_exists('sg_table_cell_attributes')) {
    function sg_table_cell_attributes(array $cell): string
    {
        $attrs = [];
        $scope = (string) ($cell['scope'] ?? '');

        if (($cell['type'] ?? 'td') === 'th' && in_array($scope, ['col', 'row', 'colgroup', 'rowgroup'], true)) {
            $attrs[] = 'scope="' . esc_attr($scope) . '"';
        }

        if (!empty($cell['id'])) {
            $id = sanitize_html_class((string) $cell['id']);
            if ($id !== '') {
                $attrs[] = 'id="' . esc_attr($id) . '"';
            }
        }

        if (!empty($cell['headers'])) {
            $headers = sanitize_class_list($cell['headers']);
            if ($headers !== '') {
                $attrs[] = 'headers="' . esc_attr($headers) . '"';
            }
        }

        if (!empty($cell['colspan'])) {
            $colspan = min(100, max(1, (int) $cell['colspan']));
            $attrs[] = 'colspan="' . $colspan . '"';
        }

        if (!empty($cell['rowspan'])) {
            $rowspan = min(100, max(1, (int) $cell['rowspan']));
            $attrs[] = 'rowspan="' . $rowspan . '"';
        }

        if (!empty($cell['class'])) {
            $attrs[] = 'class="' . component::classes($cell['class']) . '"';
        }

        return $attrs ? ' ' . implode(' ', $attrs) : '';
    }
}

if (!function_exists('sg_table_render_cell')) {
    function sg_table_render_cell(array $cell): void
    {
        $tag = ($cell['type'] ?? 'td') === 'th' ? 'th' : 'td';
        $attrs = sg_table_cell_attributes($cell);
        $content = '';

        if (!empty($cell['html'])) {
            $content = starter_kses_post($cell['html']);
        } elseif (isset($cell['text'])) {
            $content = esc_html((string) $cell['text']);
        }

        echo "<{$tag}{$attrs}>{$content}</{$tag}>";
    }
}

if (!function_exists('sg_table_render_rows')) {
    function sg_table_render_rows(array $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row) || !is_array($row)) {
                continue;
            }

            echo '<tr>';
            foreach ($row as $cell) {
                sg_table_render_cell($cell);
            }
            echo '</tr>';
        }
    }
}

$args = normalize_args($args ?? null);
$classes = component::classes('table', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);
$rows = !empty($args['rows']) && is_array($args['rows']) ? $args['rows'] : [];
$caption = isset($args['caption']) && trim((string) $args['caption']) !== '' ? trim((string) $args['caption']) : '';
$has_header = array_key_exists('header', $args) ? (bool) $args['header'] : true;

if (empty($rows)) {
    return;
}

$split = sg_table_split_rows($rows, $has_header);
$head = $split['head'];
$body = $split['body'];

if (empty($head) && empty($body)) {
    return;
}

?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <div class="table__scroll" tabindex="0">
        <table>
            <?php if ($caption !== '') : ?>
                <caption><?= esc_html($caption) ?></caption>
            <?php endif ?>

            <?php if (!empty($head)) : ?>
                <thead>
                    <?php sg_table_render_rows($head) ?>
                </thead>
            <?php endif ?>

            <?php if (!empty($body)) : ?>
                <tbody>
                    <?php sg_table_render_rows($body) ?>
                </tbody>
            <?php endif ?>
        </table>
    </div>
</div>
