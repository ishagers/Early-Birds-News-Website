
var config = {
    type: Phaser.AUTO,
    width: 256,
    height: 272,
    parent: 'phaserGame',
    backgroundColor: 0x000000,
    scene: [Scene1, Scene2]
}
var game = new Phaser.Game(config);
