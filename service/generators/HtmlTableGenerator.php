<?php
namespace service\generators;

class HtmlTableGenerator
{
    public static function generateTable(array $data, string $tableId = "", array $headerNames = []): string
    {
        $html = "<table id='$tableId'>";

        $html .= '<tr>';
        foreach ($data[0] as $key => $value) {
            $header = isset($headerNames[$key]) ? $headerNames[$key] : $key;
            $html .= '<th>' . $header . '</th>';
        }
        $html .= '</tr>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . $value . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }
}
