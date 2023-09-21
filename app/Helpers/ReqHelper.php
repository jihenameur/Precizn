<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Request;

class ReqHelper
{
    public function all($request, array $keys = null): array
    {
        $data = null;
        if ($request->getContent() !== "") {
            $data = json_decode($request->getContent(), true);
        }
        if ($data == null && $keys) {
            return $this->convertKeysToNull($keys);
        }

        if ($keys === null && $data === null) {
            return [];
        }

        return $this->pop($data, $keys, false);
    }
    public function except(Request $request, array $keys = null): array
    {
        $data = null;
        if ($request->getContent() != "") {
            $data = json_decode($request->getContent(), true);
        }
        if (($keys == null && $data == null) || $data == null) {
            return [];
        }
        if ($keys == null) {
            return $data;
        }
        return $this->pop($data, $keys, true);
    }
    public function pop(array $data, array $keys = null, $pop = false): array
    {

        $results = [];
        if ($keys == null or $keys == []) {
            return $data;
        }
        foreach ($keys as $key => $value) {
            if ($pop == false) {
                $results[$value] = array_key_exists($value, $data) ? $data[$value] : null;
            } elseif (array_key_exists($value, $data)) {
                unset($data[$value]);
            }
        }
        return $pop == false ? $results : $data;
    }
    public function convertKeysToNull(array $data): array
    {

        $KeyNull = array();
        foreach ($data as $key) {
            $KeyNull[$key] = null;
        }
        return $KeyNull;
    }
}
