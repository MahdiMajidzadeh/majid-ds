<?php

/*
| Blade/HTML syntax highlighter for the code panels. Deliberately small: it
| only needs to colour tags, attributes, strings, Blade echoes and directives —
| the five things that appear in these snippets. Token classes are styled in
| the shell's stylesheet so light and dark both work.
*/

function highlight(string $code): string
{
    $out = '';
    $length = strlen($code);
    $i = 0;

    $span = fn (string $class, string $text) => '<span class="t-'.$class.'">'.htmlspecialchars($text, ENT_QUOTES).'</span>';

    while ($i < $length) {
        $rest = substr($code, $i);

        // Blade comment: {{-- … --}}
        if (str_starts_with($rest, '{{--')) {
            $end = strpos($code, '--}}', $i);
            $end = $end === false ? $length : $end + 4;
            $out .= $span('comment', substr($code, $i, $end - $i));
            $i = $end;

            continue;
        }

        // Blade echo: {{ … }} / {!! … !!}
        if (preg_match('/^(\{\{|\{!!)/', $rest, $m)) {
            $close = $m[1] === '{{' ? '}}' : '!!}';
            $end = strpos($code, $close, $i);
            $end = $end === false ? $length : $end + strlen($close);
            $out .= $span('echo', substr($code, $i, $end - $i));
            $i = $end;

            continue;
        }

        // Blade directive: @props, @if, @foreach, @toman(…)
        if (preg_match('/^@[a-zA-Z]+/', $rest, $m)) {
            $out .= $span('directive', $m[0]);
            $i += strlen($m[0]);

            continue;
        }

        // A tag: <flux:button …> or </flux:button> or <div …>
        if (preg_match('/^<\/?([a-zA-Z][a-zA-Z0-9:._-]*)/', $rest, $m)) {
            $out .= $span('punct', str_starts_with($m[0], '</') ? '</' : '<');
            $out .= $span('tag', $m[1]);
            $i += strlen($m[0]);

            // Attributes up to the closing bracket.
            while ($i < $length && $code[$i] !== '>') {
                $rest = substr($code, $i);

                if (preg_match('/^\s+/', $rest, $m)) {
                    $out .= htmlspecialchars($m[0], ENT_QUOTES);
                    $i += strlen($m[0]);

                    continue;
                }

                if (preg_match('/^:?[a-zA-Z@][a-zA-Z0-9:._-]*/', $rest, $m)) {
                    $out .= $span('attr', $m[0]);
                    $i += strlen($m[0]);

                    continue;
                }

                if ($code[$i] === '=') {
                    $out .= $span('equals', '=');
                    $i++;

                    continue;
                }

                if ($code[$i] === '"' || $code[$i] === "'") {
                    $quote = $code[$i];
                    $end = strpos($code, $quote, $i + 1);
                    $end = $end === false ? $length - 1 : $end;
                    $out .= $span('string', substr($code, $i, $end - $i + 1));
                    $i = $end + 1;

                    continue;
                }

                if ($code[$i] === '/') {
                    $out .= $span('punct', '/');
                    $i++;

                    continue;
                }

                $out .= htmlspecialchars($code[$i], ENT_QUOTES);
                $i++;
            }

            if ($i < $length) {
                $out .= $span('punct', '>');
                $i++;
            }

            continue;
        }

        // Plain text between tags.
        $next = strcspn(substr($code, $i), '<{@');
        $next = $next === 0 ? 1 : $next;
        $out .= $span('text', substr($code, $i, $next));
        $i += $next;
    }

    return $out;
}
