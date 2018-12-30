<?php
namespace EventStore\StreamFeed;

use EventStore\ValueObjects\Identity\UUID;

/**
 * Class StreamFeed.
 */
final class StreamFeed
{
    use HasLinks;

    /**
     * @var array
     */
    private $json;

    /**
     * @var EntryEmbedMode
     */
    private $entryEmbedMode;

    /**
     * @param array          $jsonFeed
     * @param EntryEmbedMode $embedMode
     */
    public function __construct(array $jsonFeed, EntryEmbedMode $embedMode = null)
    {
        if (null === $embedMode) {
            $embedMode = EntryEmbedMode::NONE();
        }

        $this->entryEmbedMode = $embedMode;
        $this->json = $jsonFeed;
    }

    /**
     * @return EntryWithEvent[]
     */
    public function getEntries()
    {
        if($this->entryEmbedMode->sameValueAs(EntryEmbedMode::BODY()))
        {
            return array_map(
                function (array $jsonEntry) {
                    $entry = new Entry($jsonEntry);

                    $event = new Event(
                        $jsonEntry['eventType'],
                        $jsonEntry['positionEventNumber'],
                        json_decode($jsonEntry['data'], true),
                        json_decode($jsonEntry['metaData'], true),
                        new UUID($jsonEntry['eventId'])
                    );

                    return new EntryWithEvent(
                        $entry,
                        $event
                    );
                },
                $this->json['entries']
            );
        }

        return array_map(
            function (array $jsonEntry) {
                return new Entry($jsonEntry);
            },
            $this->json['entries']
        );
    }

    /**
     * @return EntryEmbedMode
     */
    public function getEntryEmbedMode()
    {
        return $this->entryEmbedMode;
    }

    /**
     * @return array
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @return array
     */
    protected function getLinks()
    {
        return $this->json['links'];
    }
}
