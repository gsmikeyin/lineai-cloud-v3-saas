<?php

namespace App\Services;

class DifyDslSanitizer
{
    /**
     * 對整份 DSL 做自動修復
     */
    public function sanitize(array &$dslData): void
    {
        // 1. 先做通用 object / array / scalar 修復
        $this->normalizeTypes($dslData);

        // 2. 再做 Dify 節點規則修復
        $this->sanitizeWorkflowNodes($dslData);
    }

    /**
     * 通用型別修復：
     * - stdClass / object => array
     * - 遞迴修正所有子層
     */
    protected function normalizeTypes(&$data): void
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (is_array($data)) {
            foreach ($data as &$value) {
                $this->normalizeTypes($value);
            }
        }
    }

    /**
     * 針對 Dify Workflow 節點修復
     */
    protected function sanitizeWorkflowNodes(array &$dslData): void
    {
        $nodes = &$dslData['workflow']['graph']['nodes'];

        if (!isset($nodes) || !is_array($nodes)) {
            return;
        }

        foreach ($nodes as &$node) {
            if (!is_array($node)) {
                continue;
            }

            $type = data_get($node, 'data.type');

            match ($type) {
                'knowledge-retrieval' => $this->sanitizeKnowledgeRetrievalNode($node),
                default => $this->sanitizeGenericNode($node),
            };
        }
    }

    /**
     * Knowledge Retrieval 節點修復
     */
    protected function sanitizeKnowledgeRetrievalNode(array &$node): void
    {
        $data = &$node['data'];

        if (!is_array($data)) {
            $data = [];
        }

        // query_variable_selector: list[str] | None | str
        $data['query_variable_selector'] = $this->normalizeSelector(
            $data['query_variable_selector'] ?? null
        );

        // query_attachment_selector: list[str] | None | str
        $data['query_attachment_selector'] = $this->normalizeSelector(
            $data['query_attachment_selector'] ?? null
        );

        // dataset_ids: 必須是 list[str]
        $data['dataset_ids'] = $this->normalizeStringList(
            $data['dataset_ids'] ?? []
        );

        // retrieval_mode 預設
        if (empty($data['retrieval_mode'])) {
            $data['retrieval_mode'] = 'multiple';
        }

        // metadata_filtering_mode 預設
        if (!isset($data['metadata_filtering_mode']) || $data['metadata_filtering_mode'] === '') {
            $data['metadata_filtering_mode'] = 'disabled';
        }

        // 防止 vision 或 config 欄位被錯轉
        if (isset($data['vision']) && !is_array($data['vision'])) {
            $data['vision'] = [];
        }

        if (isset($data['multiple_retrieval_config']) && !is_array($data['multiple_retrieval_config'])) {
            $data['multiple_retrieval_config'] = null;
        }

        if (isset($data['single_retrieval_config']) && !is_array($data['single_retrieval_config'])) {
            $data['single_retrieval_config'] = null;
        }
    }

    /**
     * 通用節點修復
     */
    protected function sanitizeGenericNode(array &$node): void
    {
        if (!isset($node['data']) || !is_array($node['data'])) {
            $node['data'] = [];
        }

        // 常見 selector 欄位可順便修
        foreach ([
            'variable_selector',
            'query_variable_selector',
            'query_attachment_selector',
        ] as $field) {
            if (array_key_exists($field, $node['data'])) {
                $node['data'][$field] = $this->normalizeSelector($node['data'][$field]);
            }
        }
    }

    /**
     * selector 類欄位：
     * 合法型別應是 string / list[string] / null
     */
    protected function normalizeSelector($value)
    {
        if (is_null($value)) {
            return null;
        }

        // object 一律轉 null，避免變成 {}
        if (is_object($value)) {
            return null;
        }

        // 空字串當 null
        if ($value === '') {
            return null;
        }

        // 純字串可直接保留
        if (is_string($value)) {
            return $value;
        }

        // array 要確保是 list[string]
        if (is_array($value)) {
            // 空陣列可保留 []
            if ($value === []) {
                return [];
            }

            $result = [];
            foreach ($value as $item) {
                if (is_scalar($item)) {
                    $result[] = (string) $item;
                }
            }
            return $result;
        }

        // 其他型別全部丟 null
        return null;
    }

    /**
     * 轉成 list[string]
     */
    protected function normalizeStringList($value): array
    {
        if (is_object($value) || is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return $value === '' ? [] : [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item) && $item !== '') {
                $result[] = (string) $item;
            }
        }

        return array_values($result);
    }
}