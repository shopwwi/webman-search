<?php

namespace Shopwwi\WebmanMeilisearch\TraitFace;

trait SettingsTrait
{
    public function getTasks()
    {
        return $this->meilisearch->index($this->index)->getTasks();
    }
    public function getTask($id)
    {
       return $this->meilisearch->index($this->index)->getTask($id);
    }

    public function getKeys()
    {
        return $this->meilisearch->getKeys();
    }
    public function getKey($key)
    {
        return $this->meilisearch->getKey($key);
    }

    public function createKey($set = [])
    {
        return $this->meilisearch->createKey($set);
    }

    public function updateKey($key,$set)
    {
        return $this->meilisearch->updateKey($key,$set);
    }

    public function deleteKey($key)
    {
        return $this->meilisearch->deleteKey($key);
    }

    public function stats()
    {
        return $this->meilisearch->index($this->index)->stats();
    }

    public function health()
    {
        return $this->meilisearch->health();
    }

    public function version()
    {
        return $this->meilisearch->version();
    }

    public function createDump()
    {
        return $this->meilisearch->createDump();
    }

    public function getDumpStatus($uid)
    {
        return $this->meilisearch->getDumpStatus($uid);
    }
    
    public function getRankingRules(): array
    {
        return $this->meilisearch->index($this->index)->getRankingRules();
    }

    public function updateRankingRules(array $rankingRules): array
    {
        return $this->meilisearch->index($this->index)->updateRankingRules($rankingRules);
    }

    public function resetRankingRules(): array
    {
        return $this->meilisearch->index($this->index)->resetRankingRules();
    }

    public function getDistinctAttribute()
    {
        return $this->meilisearch->index($this->index)->getDistinctAttribute();
    }

    public function updateDistinctAttribute(string $distinctAttribute): array
    {
        return $this->meilisearch->index($this->index)->updateDistinctAttribute($distinctAttribute);
    }

    public function resetDistinctAttribute(): array
    {
        return $this->meilisearch->index($this->index)->resetDistinctAttribute();
    }

    // Settings - Searchable attributes

    public function getSearchableAttributes(): array
    {
        return $this->meilisearch->index($this->index)->getSearchableAttributes();
    }

    public function updateSearchableAttributes(array $searchableAttributes): array
    {
        return $this->meilisearch->index($this->index)->updateSearchableAttributes($searchableAttributes);
    }

    public function resetSearchableAttributes(): array
    {
        return $this->meilisearch->index($this->index)->resetSearchableAttributes();
    }

    // Settings - Displayed attributes

    public function getDisplayedAttributes(): array
    {
        return $this->meilisearch->index($this->index)->getDisplayedAttributes();
    }

    public function updateDisplayedAttributes(array $displayedAttributes): array
    {
        return $this->meilisearch->index($this->index)->updateDisplayedAttributes($displayedAttributes);
    }

    public function resetDisplayedAttributes(): array
    {
        return $this->meilisearch->index($this->index)->resetDisplayedAttributes();
    }

    // Settings - Stop-words

    public function getStopWords(): array
    {
        return $this->meilisearch->index($this->index)->getStopWords();
    }

    public function updateStopWords(array $stopWords): array
    {
        return $this->meilisearch->index($this->index)->updateStopWords($stopWords);
    }

    public function resetStopWords(): array
    {
        return $this->meilisearch->index($this->index)->resetStopWords();
    }

    // Settings - Synonyms

    public function getSynonyms(): array
    {
        return $this->meilisearch->index($this->index)->getSynonyms();
    }

    public function updateSynonyms(array $synonyms): array
    {
        return $this->meilisearch->index($this->index)->updateSynonyms($synonyms);
    }

    public function resetSynonyms(): array
    {
        return $this->meilisearch->index($this->index)->resetSynonyms();
    }

    // Settings - Filterable Attributes

    public function getFilterableAttributes(): array
    {
        return $this->meilisearch->index($this->index)->getFilterableAttributes();
    }

    public function updateFilterableAttributes(array $filterableAttributes): array
    {
        return $this->meilisearch->index($this->index)->updateFilterableAttributes($filterableAttributes);
    }

    public function resetFilterableAttributes(): array
    {
        return $this->meilisearch->index($this->index)->resetFilterableAttributes();
    }

    // Settings - Sortable Attributes

    public function getSortableAttributes(): array
    {
        return $this->meilisearch->index($this->index)->getSortableAttributes();
    }

    public function updateSortableAttributes(array $sortableAttributes): array
    {
        return $this->meilisearch->index($this->index)->updateSortableAttributes($sortableAttributes);
    }

    public function resetSortableAttributes(): array
    {
        return $this->meilisearch->index($this->index)->resetSortableAttributes();
    }
    public function getSettings(): array
    {
        return $this->meilisearch->index($this->index)->getSettings();
    }

    public function updateSettings($settings): array
    {
        return $this->meilisearch->index($this->index)->updateSettings($settings);
    }

    public function resetSettings(): array
    {
        return $this->meilisearch->index($this->index)->resetSettings();
    }
}