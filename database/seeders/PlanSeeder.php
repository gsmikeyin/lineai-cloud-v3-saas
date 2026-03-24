<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'code' => 'starter',
                'price_monthly' => 1490,
                'price_yearly' => 14900,
                'max_customers' => 1000,
                'max_team_members' => 1,
                'max_monthly_ai_replies' => 1000,
                'max_monthly_broadcasts' => 1000,
                'max_automation_rules' => 1,
                'supports_campaigns' => false,
                'supports_analytics' => false,
                'supports_team' => false,
            ],
            [
                'name' => 'Growth',
                'code' => 'growth',
                'price_monthly' => 3490,
                'price_yearly' => 34900,
                'max_customers' => 5000,
                'max_team_members' => 3,
                'max_monthly_ai_replies' => 5000,
                'max_monthly_broadcasts' => 5000,
                'max_automation_rules' => 5,
                'supports_campaigns' => true,
                'supports_analytics' => true,
                'supports_team' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
