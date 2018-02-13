#include <QCoreApplication>
#include <QTimer>
#include "mainclass.h"

int main(int argc, char *argv[]) {
    QCoreApplication app(argc, argv);

    // create the main class
    MainClass myMain;

    // connect up the signals
    QObject::connect(&myMain, SIGNAL(finished()),
            &app, SLOT(quit()));
    QObject::connect(&app, SIGNAL(aboutToQuit()),
            &myMain, SLOT(aboutToQuitApp()));

    // This code will start the messaging engine in QT and in
    // 10ms it will start the execution in the MainClass.run routine;
    QTimer::singleShot(10, &myMain, SLOT(run()));
    return app.exec();
}
