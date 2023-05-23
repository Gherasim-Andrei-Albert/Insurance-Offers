<?php

declare(strict_types=1);

enum Tariff {
    case basic;
    case complex;
}

enum Currency {
  case euro;
}

class CurrencyValue{
  public float $value;
  public Currency $currency;

  function __construct(float $value, Currency $currency){
    $this->value = $value;
    $this->currency = $currency;
  }
}

class offer {
  public Tariff $tariff;
  public int $coverageSum;
  public int $duration;
  public int $deductible;
  public int $paymentInterval;
  public CurrencyValue $grossPremium;
  public CurrencyValue $netPremium;

  static array $coverageSumOptions = array(1000000, 3000000);
  static array $durationMonthsOptions = array(12, 36);
  static array $deductibleOptions = array(0, 250);
  static array $paymentIntervalMonthsOptions = array(12, 6, 3, 1);

  function __construct(
    Tariff $tariff, 
    int $coverageSum, 
    int $duration,
    int $deductible,
    int $paymentInterval
  ) {
    $this->tariff = $tariff;
    $this->coverageSum = $coverageSum;
    $this->duration = $duration;
    $this->deductible = $deductible;
    $this->paymentInterval = $paymentInterval;

    $yearlyNet = 1000;
    if($this->tariff == Tariff::complex){
      $yearlyNet += 200;
    }
    if($this->coverageSum == 3000000){
      $yearlyNet += .30 * $yearlyNet;
    }
    if($this->duration / 12 == 3){
      $yearlyNet -= .10 * $yearlyNet;
    }
    if($this->deductible == 250){
      $yearlyNet -= .15 * $yearlyNet;
    }
    if($this->paymentInterval == 6){
      $yearlyNet += .03 * $yearlyNet;
    }
    if($this->paymentInterval == 3){
      $yearlyNet += .05 * $yearlyNet;
    }
    if($this->paymentInterval == 1){
      $yearlyNet += .08 * $yearlyNet;
    }

    $netValue = $yearlyNet;
    if ($this->paymentInterval != 12) {
      $netValue = $yearlyNet / 12;
    }
    $this->netPremium = new CurrencyValue(
      $netValue, Currency::euro
    );

    $grossValue = 
      $this->netPremium->value 
      + 0.19 * $this->netPremium->value;
    $this->grossPremium = new CurrencyValue(
      $grossValue, Currency::euro
    );
  }
}
// // In case coverageSum and deductibe would be currency dependent
// 
// offer::$coverageSumOptions = array(
//   new CurrencyValue(1000000, Currency::euro),
//   new CurrencyValue(3000000, Currency::euro),
// );
// offer::$deductibleOptions = array(
//   new CurrencyValue(0, Currency::euro),
//   new CurrencyValue(250, Currency::euro), 
// );

$offers = [];

foreach (Tariff::cases() as $tariff) {
  foreach (offer::$coverageSumOptions as $coverageSum) {
    foreach (offer::$durationMonthsOptions as $duration) {
      foreach (offer::$deductibleOptions as $deductible) {
        foreach (offer::$paymentIntervalMonthsOptions as $paymentInterval) {
          $offers[] = new offer(
            $tariff,
            $coverageSum, 
            $duration,
            $deductible,
            $paymentInterval
          );
        }
      }
    }
  }
}

foreach ($offers as $offerIndex => $offer) {

  $offerNr = $offerIndex + 1;
  $offer = $offers[$offerIndex];
  $formatedNetPremium =
    number_format(
      (float) (floor(($offer->netPremium->value * 100)) / 100),
      2,
      '.',
      ''
    );
  $formatedGrossPremium =
    number_format(
      (float) (floor(($offer->grossPremium->value * 100)) / 100),
      2,
      '.',
      ''
    );

  echo(htmlentities("oferta{$offerNr}:"));
  echo("<br/>");
  echo(htmlentities("tariff={$offer->tariff->name}"));
  echo("<br/>");
  echo(htmlentities("coverageSum={$offer->coverageSum}"));
  echo("<br/>");
  echo(htmlentities("duration={$offer->duration}"));
  echo("<br/>");
  echo(htmlentities("deductible={$offer->deductible}"));
  echo("<br/>");
  echo(htmlentities("paymentInterval={$offer->paymentInterval}"));
  echo("<br/>");
  echo(htmlentities("grossPremium={$formatedGrossPremium}"));
  echo("<br/>");
  echo(htmlentities("netPremium={$formatedNetPremium}"));
  echo("<br/>");
  echo("<br/>");

}

// // Tried a recursive implementation for the nested foreach
//
// function generateoffersForProperties(array $properties)
// {
//   [ 
//     $tariff,
//     $coverageSum, 
//     $duration,
//     $deductible,
//     $paymentInterval,
//     $grossPremium,
//     $netPremium,
//   ] = $properties + array_fill(0, 7, null);

//   $combinations = [];

//   if ($tariff == null)
//     foreach (Tariff::cases() as $tariff) {
//       $combinations[] = generateoffersForProperties($properties + array($tariff));
//     }

//   return $combinations;
// }

// function generateoffers()
// {
//   $combinations = generateoffersForProperties(array());

//   var_dump($combinations);
// }

// generateoffers();