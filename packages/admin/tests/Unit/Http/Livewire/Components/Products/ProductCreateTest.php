<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Products;

use GetCandy\Hub\Http\Livewire\Components\Products\ProductCreate;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.products
 */
class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);

        Language::factory()->create([
            'default' => false,
            'code'    => 'fr',
        ]);

        Currency::factory()->create([
            'default'        => true,
            'decimal_places' => 2,
        ]);

        TaxClass::factory()->create([
            'default' => true,
        ]);

        ProductType::factory()->create();
    }

    /** @test  */
    public function component_mounts_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class);
    }

    /** @test */
    public function can_create_product()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::getDefault();

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set('variant.sku', '1234')
            ->set('variant.tax_ref', 'CUSTOMTAX')
            ->set("basePrices.{$currency->code}.price", 1234)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas((new ProductVariant)->getTable(), [
            'sku' => '1234',
            'tax_ref' => 'CUSTOMTAX',
        ]);

        $this->assertDatabaseHas((new Price)->getTable(), [
            'price' => '123400',
        ]);

        $this->assertDatabaseCount((new Price)->getTable(), 1);
    }
}
