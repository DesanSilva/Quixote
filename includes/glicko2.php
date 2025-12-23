<?php
/**
 * Glicko-2 Rating System Implementation
 * Based on Mark Glickman's rating system for chess
 * http://www.glicko.net/glicko/glicko2.pdf
 */

class Glicko2 {
    // System constant: constrains the change in volatility over time
    const TAU = 0.5;
    
    // Glicko-2 scale conversion constant
    const SCALE = 173.7178;
    
    /**
     * Calculate new ratings for two players after a game
     * Uses Glicko-2 algorithm to update ratings based on game outcome
     * @param array $player1 Player 1 data (rating, ratingDeviation, volatility)
     * @param array $player2 Player 2 data (rating, ratingDeviation, volatility)
     * @param float $score1 Score for player 1 (1=win, 0.5=draw, 0=loss)
     * @return array Updated ratings for both players
     */
    public static function calculateNewRatings($player1, $player2, $score1) {
        // Convert to Glicko-2 scale
        $mu1 = self::ratingToMu($player1['rating']);
        $phi1 = self::rdToPhi($player1['ratingDeviation']);
        $sigma1 = $player1['volatility'];
        
        $mu2 = self::ratingToMu($player2['rating']);
        $phi2 = self::rdToPhi($player2['ratingDeviation']);
        $sigma2 = $player2['volatility'];
        
        // Calculate new ratings for player 1
        $newRating1 = self::updateRating($mu1, $phi1, $sigma1, [
            ['mu' => $mu2, 'phi' => $phi2, 'score' => $score1]
        ]);
        
        // Calculate new ratings for player 2 (score is inverted)
        $score2 = 1 - $score1;
        $newRating2 = self::updateRating($mu2, $phi2, $sigma2, [
            ['mu' => $mu1, 'phi' => $phi1, 'score' => $score2]
        ]);
        
        return [
            'player1' => [
                'rating' => self::muToRating($newRating1['mu']),
                'ratingDeviation' => self::phiToRd($newRating1['phi']),
                'volatility' => $newRating1['sigma']
            ],
            'player2' => [
                'rating' => self::muToRating($newRating2['mu']),
                'ratingDeviation' => self::phiToRd($newRating2['phi']),
                'volatility' => $newRating2['sigma']
            ]
        ];
    }
    
    /**
     * Update a player's rating based on game results
     */
    private static function updateRating($mu, $phi, $sigma, $opponents) {
        // Step 2: Compute v (estimated variance)
        $v = self::computeV($mu, $opponents);
        
        // Step 3: Compute delta (improvement in rating)
        $delta = self::computeDelta($mu, $v, $opponents);
        
        // Step 4: Compute new volatility
        $newSigma = self::computeNewVolatility($phi, $sigma, $v, $delta);
        
        // Step 5: Update rating deviation
        $phiStar = sqrt($phi * $phi + $newSigma * $newSigma);
        
        // Step 6: Update rating and RD
        $newPhi = 1 / sqrt(1 / ($phiStar * $phiStar) + 1 / $v);
        
        $sum = 0;
        foreach ($opponents as $opp) {
            $g = self::g($opp['phi']);
            $E = self::E($mu, $opp['mu'], $opp['phi']);
            $sum += $g * ($opp['score'] - $E);
        }
        $newMu = $mu + $newPhi * $newPhi * $sum;
        
        return [
            'mu' => $newMu,
            'phi' => $newPhi,
            'sigma' => $newSigma
        ];
    }
    
    /**
     * Compute estimated variance
     */
    private static function computeV($mu, $opponents) {
        $sum = 0;
        foreach ($opponents as $opp) {
            $g = self::g($opp['phi']);
            $E = self::E($mu, $opp['mu'], $opp['phi']);
            $sum += $g * $g * $E * (1 - $E);
        }
        return 1 / $sum;
    }
    
    /**
     * Compute delta (improvement in rating)
     */
    private static function computeDelta($mu, $v, $opponents) {
        $sum = 0;
        foreach ($opponents as $opp) {
            $g = self::g($opp['phi']);
            $E = self::E($mu, $opp['mu'], $opp['phi']);
            $sum += $g * ($opp['score'] - $E);
        }
        return $v * $sum;
    }
    
    /**
     * Compute new volatility using Illinois algorithm
     */
    private static function computeNewVolatility($phi, $sigma, $v, $delta) {
        $a = log($sigma * $sigma);
        $tau = self::TAU;
        
        $f = function($x) use ($phi, $v, $delta, $a, $tau) {
            $ex = exp($x);
            $phiSquare = $phi * $phi;
            $deltaSquare = $delta * $delta;
            
            $part1 = $ex * ($deltaSquare - $phiSquare - $v - $ex);
            $part2 = 2 * pow($phiSquare + $v + $ex, 2);
            
            $part3 = ($x - $a) / ($tau * $tau);
            
            return $part1 / $part2 - $part3;
        };
        
        // Find bounds
        $A = $a;
        $deltaSquare = $delta * $delta;
        $phiSquare = $phi * $phi;
        
        if ($deltaSquare > $phiSquare + $v) {
            $B = log($deltaSquare - $phiSquare - $v);
        } else {
            $k = 1;
            while ($f($a - $k * $tau) < 0) {
                $k++;
            }
            $B = $a - $k * $tau;
        }
        
        // Use Illinois algorithm to find new volatility
        $fA = $f($A);
        $fB = $f($B);
        
        while (abs($B - $A) > 0.000001) {
            $C = $A + ($A - $B) * $fA / ($fB - $fA);
            $fC = $f($C);
            
            if ($fC * $fB < 0) {
                $A = $B;
                $fA = $fB;
            } else {
                $fA = $fA / 2;
            }
            
            $B = $C;
            $fB = $fC;
        }
        
        return exp($A / 2);
    }
    
    /**
     * g function: measures impact of opponent's RD
     */
    private static function g($phi) {
        return 1 / sqrt(1 + 3 * $phi * $phi / (M_PI * M_PI));
    }
    
    /**
     * E function: expected score
     */
    private static function E($mu, $muJ, $phiJ) {
        return 1 / (1 + exp(-self::g($phiJ) * ($mu - $muJ)));
    }
    
    /**
     * Convert Glicko rating to Glicko-2 mu
     */
    private static function ratingToMu($rating) {
        return ($rating - 1500) / self::SCALE;
    }
    
    /**
     * Convert Glicko-2 mu to Glicko rating
     */
    private static function muToRating($mu) {
        return $mu * self::SCALE + 1500;
    }
    
    /**
     * Convert Glicko RD to Glicko-2 phi
     */
    private static function rdToPhi($rd) {
        return $rd / self::SCALE;
    }
    
    /**
     * Convert Glicko-2 phi to Glicko RD
     */
    private static function phiToRd($phi) {
        return $phi * self::SCALE;
    }
    
    /**
     * Get initial rating values for a new player
     */
    public static function getInitialRating() {
        return [
            'rating' => 1500.0,
            'ratingDeviation' => 350.0,
            'volatility' => 0.06
        ];
    }
}
?>
