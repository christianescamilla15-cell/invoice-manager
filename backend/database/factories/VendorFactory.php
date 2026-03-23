<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $states = ['Aguascalientes', 'Jalisco', 'Nuevo León', 'Ciudad de México', 'Puebla', 'Querétaro', 'Guanajuato', 'Sonora', 'Chihuahua', 'Yucatán'];
        $cities = ['Guadalajara', 'Monterrey', 'CDMX', 'Puebla', 'Querétaro', 'León', 'Hermosillo', 'Chihuahua', 'Mérida', 'Aguascalientes'];

        return [
            'rfc' => strtoupper($this->faker->bothify('???######???')),
            'business_name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->numerify('##########'),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement($cities),
            'state' => $this->faker->randomElement($states),
            'zip_code' => $this->faker->numerify('#####'),
        ];
    }
}
