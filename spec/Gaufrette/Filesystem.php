<?php

namespace spec\Gaufrette;

use PHPSpec2\ObjectBehavior;

class Filesystem extends ObjectBehavior
{
    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function let($adapter)
    {
        $this->beConstructedWith($adapter);
    }

    function it_should_be_initializable()
    {
        $this->shouldBeAnInstanceOf('Gaufrette\Filesystem');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_gives_access_to_adapter($adapter)
    {
        $this->getAdapter()->shouldBe($adapter);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_check_is_file_exists_using_adapter($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->exists('otherFilename')->willReturn(false);

        $this->has('filename')->shouldReturn(true);
        $this->has('otherFilename')->shouldReturn(false);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_rename_file($adapter)
    {
        $adapter->exists('filename')->shouldBeCalled()->willReturn(true);
        $adapter->exists('otherFilename')->shouldBeCalled()->willReturn(false);
        $adapter->rename('filename', 'otherFilename')->shouldBeCalled()->willReturn(true);

        $this->rename('filename', 'otherFilename')->shouldReturn(true);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_fail_when_renamed_source_file_does_not_exist($adapter)
    {
        $adapter->exists('filename')->willReturn(false);

        $this
            ->shouldThrow(new \Gaufrette\Exception\FileNotFound('filename'))
            ->duringRename('filename', 'otherFilename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_fail_when_renamed_target_file_exists($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->exists('otherFilename')->willReturn(true);

        $this
            ->shouldThrow(new \Gaufrette\Exception\UnexpectedFile('otherFilename'))
            ->duringRename('filename', 'otherFilename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_fail_when_rename_is_not_successful($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->exists('otherFilename')->willReturn(false);
        $adapter->rename('filename', 'otherFilename')->willReturn(false);

        $this
            ->shouldThrow(new \RuntimeException('Could not rename the "filename" key to "otherFilename".'))
            ->duringRename('filename', 'otherFilename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_get_file_object($adapter)
    {
        $adapter->exists('filename')->willReturn(true);

        $this->get('filename')->shouldBeAnInstanceOf('Gaufrette\File');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_not_get_file_object_when_file_does_not_exist($adapter)
    {
        $adapter->exists('filename')->willReturn(false);

        $this
            ->shouldThrow(new \Gaufrette\Exception\FileNotFound('filename'))
            ->duringGet('filename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_get_file_object_when_file_does_not_exist_but_can_be_created($adapter)
    {
        $adapter->exists('filename')->willReturn(false);

        $this->get('filename', true)->shouldBeAnInstanceOf('Gaufrette\File');
    }

    /**
     * @param \spec\Gaufrette\Adapter $extendedAdapter
     * @param \Gaufrette\File $file
     */
    function it_should_delegate_file_creation_to_adapter_when_adapter_is_file_factory($extendedAdapter, $file)
    {
        $this->beConstructedWith($extendedAdapter);
        $extendedAdapter->exists('filename')->willReturn(true);
        $extendedAdapter->createFile('filename', $this)->willReturn($file);

        $this->get('filename')->shouldBe($file);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_write_content_to_new_file($adapter)
    {
        $adapter->exists('filename')->shouldBeCalled()->willReturn(false);
        $adapter->write('filename', 'some content to write')->shouldBeCalled()->willReturn(21);

        $this->write('filename', 'some content to write')->shouldReturn(21);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_update_content_of_file($adapter)
    {
        $adapter->write('filename', 'some content to write')->shouldBeCalled()->willReturn(21);

        $this->write('filename', 'some content to write', true)->shouldReturn(21);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_not_update_content_of_file_when_file_cannot_be_overwrite($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->write('filename', 'some content to write')->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('The key "filename" already exists and can not be overwritten.'))
            ->duringWrite('filename', 'some content to write');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_fail_when_write_is_not_successful($adapter)
    {
        $adapter->exists('filename')->willReturn(false);
        $adapter->write('filename', 'some content to write')->shouldBeCalled()->willReturn(false);

        $this
            ->shouldThrow(new \RuntimeException('Could not write the "filename" key content.'))
            ->duringWrite('filename', 'some content to write');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_read_file($adapter)
    {
        $adapter->exists('filename')->shouldBeCalled()->willReturn(true);
        $adapter->read('filename')->shouldBeCalled()->willReturn('Some content');

        $this->read('filename')->shouldReturn('Some content');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_not_read_file_which_does_not_exist($adapter)
    {
        $adapter->exists('filename')->willReturn(false);

        $this
            ->shouldThrow(new \Gaufrette\Exception\FileNotFound('filename'))
            ->duringRead('filename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_fail_when_read_is_not_successful($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->read('filename')->willReturn(false);

        $this
            ->shouldThrow(new \RuntimeException('Could not read the "filename" key content.'))
            ->duringRead('filename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_delete_file($adapter)
    {
        $adapter->exists('filename')->shouldBeCalled()->willReturn(true);
        $adapter->delete('filename')->shouldBeCalled()->willReturn(true);

        $this->delete('filename')->shouldReturn(true);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_not_delete_file_which_does_not_exist($adapter)
    {
        $adapter->exists('filename')->willReturn(false);

        $this
            ->shouldThrow(new \Gaufrette\Exception\FileNotFound('filename'))
            ->duringDelete('filename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_fail_when_delete_is_not_successful($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->delete('filename')->willReturn(false);

        $this
            ->shouldThrow(new \RuntimeException('Could not remove the "filename" key.'))
            ->duringDelete('filename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_get_all_keys($adapter)
    {
        $keys = array('filename', 'filename1', 'filename2');
        $adapter->keys()->willReturn($keys);

        $this->keys()->shouldReturn($keys);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_match_listed_keys_using_specified_pattern($adapter)
    {
        $keys = array('filename', 'filename1', 'filename2', 'testKey', 'KeyTest', 'testkey');

        $adapter->keys()->willReturn($keys);
        $adapter->isDirectory(ANY_ARGUMENT)->willReturn(false);

        $this->listKeys()->shouldReturn(
            array(
                'keys' => array('filename', 'filename1', 'filename2', 'testKey', 'KeyTest', 'testkey'),
                'dirs' => array()
            )
        );
        $this->listKeys('filename')->shouldReturn(
            array(
                'keys' => array('filename', 'filename1', 'filename2'),
                'dirs' => array()
            )
        );
        $this->listKeys('Key')->shouldReturn(
            array(
                'keys' => array('testKey', 'KeyTest'),
                'dirs' => array()
            )
        );
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_decide_which_key_is_dir_using_adapter($adapter)
    {
        $keys = array('filename', 'filename1', 'filename2', 'testKey', 'KeyTest', 'testkey');

        $adapter->keys()->willReturn($keys);
        $adapter->isDirectory('filename')->willReturn(false);
        $adapter->isDirectory('filename2')->willReturn(false);
        $adapter->isDirectory('KeyTest')->willReturn(false);
        $adapter->isDirectory('testkey')->willReturn(false);

        $adapter->isDirectory('filename1')->willReturn(true);
        $adapter->isDirectory('testKey')->willReturn(true);

        $this->listKeys()->shouldReturn(
            array(
                'keys' => array('filename', 'filename2', 'KeyTest', 'testkey'),
                'dirs' => array('filename1', 'testKey')
            )
        );
        $this->listKeys('filename')->shouldReturn(
            array(
                'keys' => array('filename', 'filename2'),
                'dirs' => array('filename1')
            )
        );
        $this->listKeys('Key')->shouldReturn(
            array(
                'keys' => array('KeyTest'),
                'dirs' => array('testKey')
            )
        );
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_get_mtime_of_file_using_adapter($adapter)
    {
        $adapter->exists('filename')->willReturn(true);
        $adapter->mtime('filename')->willReturn(1234567);

        $this->mtime('filename')->shouldReturn(1234567);
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_not_get_mtime_of_file_which_does_not_exist($adapter)
    {
        $adapter->exists('filename')->willReturn(false);

        $this
            ->shouldThrow(new \Gaufrette\Exception\FileNotFound('filename'))
            ->duringMtime('filename');
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_calculate_file_checksum($adapter)
    {
        $adapter->exists('filename')->shouldBeCalled()->willReturn(true);
        $adapter->read('filename')->willReturn('some content');

        $this->checksum('filename')->shouldReturn(md5('some content'));
    }

    /**
     * @param \Gaufrette\Adapter $adapter
     */
    function it_should_not_calculate_checksum_of_file_which_does_not_exist($adapter)
    {
        $adapter->exists('filename')->shouldBeCalled()->willReturn(false);

        $this
            ->shouldThrow(new \Gaufrette\Exception\FileNotFound('filename'))
            ->duringChecksum('filename');
    }

    /**
     * @param \spec\Gaufrette\Adapter $extendedAdapter
     */
    function it_should_delegate_checksum_calculation_to_adapter_when_adapter_is_checksum_calculator($extendedAdapter)
    {
        $this->beConstructedWith($extendedAdapter);
        $extendedAdapter->exists('filename')->shouldBeCalled()->willReturn(true);
        $extendedAdapter->read('filename')->shouldNotBeCalled();
        $extendedAdapter->checksum('filename')->shouldBeCalled()->willReturn(12);

        $this->checksum('filename')->shouldReturn(12);
    }
}

interface Adapter extends \Gaufrette\Adapter,
                          \Gaufrette\Adapter\FileFactory,
                          \Gaufrette\Adapter\StreamFactory,
                          \Gaufrette\Adapter\ChecksumCalculator,
                          \Gaufrette\Adapter\MetadataSupporter
{}
