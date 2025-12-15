<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Utils_Admin {
  use Singleton_Trait;

  /**
   * Return array of adjectives for randomized username generation.
   *
   * @since 5.19.0
   * @since 5.34.0 - Moved into Utils_Admin class.
   *
   * @return string[] Array of adjectives.
   */

  public static function get_username_adjectives() : array {
    static $adjectives = array(
      'Radical', 'Tubular', 'Gnarly', 'Epic', 'Electric', 'Neon', 'Bodacious', 'Rad',
      'Totally', 'Funky', 'Wicked', 'Fresh', 'Chill', 'Groovy', 'Vibrant', 'Flashy',
      'Buff', 'Hella', 'Motor', 'Cyber', 'Pixel', 'Holo', 'Stealth', 'Synthetic',
      'Enhanced', 'Synth', 'Bio', 'Laser', 'Virtual', 'Analog', 'Mega', 'Wave', 'Solo',
      'Retro', 'Quantum', 'Robotic', 'Digital', 'Hyper', 'Punk', 'Giga', 'Electro',
      'Chrome', 'Fusion', 'Vivid', 'Stellar', 'Galactic', 'Turbo', 'Atomic', 'Cosmic',
      'Artificial', 'Kinetic', 'Binary', 'Hypersonic', 'Runic', 'Data', 'Knightly',
      'Cryonic', 'Nebular', 'Golden', 'Silver', 'Red', 'Crimson', 'Augmented', 'Vorpal',
      'Ascended', 'Serious', 'Solid', 'Master', 'Prism', 'Spinning', 'Masked', 'Hardcore',
      'Somber', 'Celestial', 'Arcane', 'Luminous', 'Ionized', 'Lunar', 'Uncanny', 'Subatomic',
      'Luminary', 'Radiant', 'Ultra', 'Starship', 'Space', 'Starlight', 'Interstellar', 'Metal',
      'Bionic', 'Machine', 'Isekai', 'Warp', 'Neo', 'Alpha', 'Power', 'Unhinged', 'Ash',
      'Savage', 'Silent', 'Screaming', 'Misty', 'Rending', 'Horny', 'Dreadful', 'Bizarre',
      'Chaotic', 'Wacky', 'Twisted', 'Manic', 'Crystal', 'Infernal', 'Ruthless', 'Grim',
      'Mortal', 'Forsaken', 'Heretical', 'Cursed', 'Blighted', 'Scarlet', 'Delightful',
      'Nuclear', 'Azure', 'Emerald', 'Amber', 'Mystic', 'Ethereal', 'Enchanted', 'Valiant',
      'Fierce', 'Obscure', 'Enigmatic'
    );

    return apply_filters( 'fictioneer_random_username_adjectives', $adjectives );
  }

  /**
   * Return array of nouns for randomized username generation.
   *
   * @since 5.19.0
   * @since 5.34.0 - Moved into Utils_Admin class.
   *
   * @return string[] Array of nouns.
   */

  public static function get_username_nouns() : array {
    static $nouns = array(
      'Avatar', 'Cassette', 'Rubiks', 'Gizmo', 'Synthwave', 'Tron', 'Replicant', 'Warrior',
      'Hacker', 'Samurai', 'Cyborg', 'Runner', 'Mercenary', 'Shogun', 'Maverick', 'Glitch',
      'Byte', 'Matrix', 'Motion', 'Shinobi', 'Circuit', 'Droid', 'Virus', 'Vortex', 'Mech',
      'Codex', 'Hologram', 'Specter', 'Intelligence', 'Technomancer', 'Rider', 'Ghost',
      'Hunter', 'Hound', 'Wizard', 'Knight', 'Rogue', 'Scout', 'Ranger', 'Paladin', 'Sorcerer',
      'Mage', 'Artificer', 'Cleric', 'Tank', 'Fighter', 'Pilot', 'Necromancer', 'Neuromancer',
      'Barbarian', 'Streetpunk', 'Phantom', 'Shaman', 'Druid', 'Dragon', 'Dancer', 'Captain',
      'Pirate', 'Snake', 'Rebel', 'Kraken', 'Spark', 'Blitz', 'Alchemist', 'Dragoon', 'Geomancer',
      'Neophyte', 'Terminator', 'Tempest', 'Enigma', 'Automaton', 'Daemon', 'Juggernaut',
      'Paragon', 'Sentinel', 'Viper', 'Velociraptor', 'Spirit', 'Punk', 'Synth', 'Biomech',
      'Engineer', 'Pentagoose', 'Vampire', 'Soldier', 'Chimera', 'Lobotomy', 'Mutant',
      'Revenant', 'Wraith', 'Chupacabra', 'Banshee', 'Fae', 'Leviathan', 'Cenobite', 'Bob',
      'Ketchum', 'Collector', 'Student', 'Lover', 'Chicken', 'Alien', 'Titan', 'Sinner',
      'Nightmare', 'Bioplague', 'Annihilation', 'Elder', 'Priest', 'Guardian', 'Quagmire',
      'Berserker', 'Oblivion', 'Decimator', 'Devastation', 'Calamity', 'Doom', 'Ruin', 'Abyss',
      'Heretic', 'Armageddon', 'Obliteration', 'Inferno', 'Torment', 'Carnage', 'Purgatory',
      'Chastity', 'Angel', 'Raven', 'Star', 'Trinity', 'Idol', 'Eidolon', 'Havoc', 'Nirvana',
      'Digitron', 'Phoenix', 'Lantern', 'Warden', 'Falcon'
    );

    return apply_filters( 'fictioneer_random_username_nouns', $nouns );
  }
}
