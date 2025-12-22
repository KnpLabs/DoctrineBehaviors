<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Provider;

use Knp\DoctrineBehaviors\Provider\LocaleProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class LocaleProviderTest extends TestCase
{
    private LocaleProvider $localeProvider;
    private RequestStack|MockObject $requestStack;
    private ParameterBagInterface|MockObject $parameterBag;
    private TranslatorInterface|MockObject $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->localeProvider = new LocaleProvider(
            $this->requestStack,
            $this->parameterBag,
            $this->translator,
        );
    }

    public function testProvideCurrentLocaleReadsFromRequest(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getLocale')
            ->willReturn('ZZ');

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->translator->expects($this->never())
            ->method('getLocale');

        $this->assertSame('ZZ', $this->localeProvider->provideCurrentLocale());
    }

    public function testProvideCurrentLocaleIgnoresRequestLocaleWhenEmpty(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getLocale')
            ->willReturn('');

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->translator->expects($this->once())
            ->method('getLocale')
            ->willReturn('ZZ');

        $this->assertSame('ZZ', $this->localeProvider->provideCurrentLocale());
    }

    public function testProvideCurrentLocaleFallsBackGracefully(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getLocale')
            ->willReturn('');

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->translator->expects($this->once())
            ->method('getLocale')
            ->willReturn('');

        $this->assertNull($this->localeProvider->provideCurrentLocale());
    }

    public function testProvideCurrentLocaleUsesTranslatorWhenNoRequestAvailable(): void
    {
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->translator->expects($this->once())
            ->method('getLocale')
            ->willReturn('ZZ');

        $this->assertSame('ZZ', $this->localeProvider->provideCurrentLocale());
    }

    public function testProvideCurrentLocaleHandlesMissingTranslatorWhenNoRequest(): void
    {
        $localeProvider = new LocaleProvider(
            $this->requestStack,
            $this->parameterBag,
            null
        );

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->assertNull($localeProvider->provideCurrentLocale());
    }

    public function testProvideCurrentLocaleHandlesMissingTranslatorWhenEmptyRequestLocale(): void
    {
        $localeProvider = new LocaleProvider(
            $this->requestStack,
            $this->parameterBag,
            null
        );

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getLocale')
            ->willReturn('');
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->assertNull($localeProvider->provideCurrentLocale());
    }

    public function testProvideFallbackLocaleReadsFromRequest(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getDefaultLocale')
            ->willReturn('ZZ');

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->assertSame('ZZ', $this->localeProvider->provideFallbackLocale());
    }

    public function testProvideFallbackLocaleReadsFromConfigWhenNoRequest(): void
    {
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->parameterBag->expects($this->once())
            ->method('has')
            ->with('locale')
            ->willReturn(true);

        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('locale')
            ->willReturn('ZZ');

        $this->assertSame('ZZ', $this->localeProvider->provideFallbackLocale());
    }

    public function testProvideFallbackLocaleFallsBackToDefaultConfiguredLocale(): void
    {
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->parameterBag->expects($this->once())
            ->method('has')
            ->with('locale')
            ->willReturn(false);

        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('kernel.default_locale')
            ->willReturn('ZZ');

        $this->assertSame('ZZ', $this->localeProvider->provideFallbackLocale());
    }

    /** @dataProvider getExpectedExceptionsProvider */
    public function testProvideFallbackLocaleHandlesConfigExceptions(Throwable $expectedException): void
    {
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->parameterBag->expects($this->once())
            ->method('has')
            ->with('locale')
            ->willReturn(false);

        $this->parameterBag->expects($this->once())
            ->method('get')
            ->willThrowException($expectedException);

        $this->assertNull($this->localeProvider->provideFallbackLocale());
    }

    public function getExpectedExceptionsProvider(): array
    {
        return [
            [new ParameterNotFoundException('foo')],
            [new InvalidArgumentException()],
        ];
    }
}
